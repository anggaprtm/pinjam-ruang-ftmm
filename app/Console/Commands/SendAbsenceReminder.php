<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Services\TelegramService;
use App\Models\User;
use App\Models\AbsensiLog;
use App\Models\BotSetting;
use App\Models\HariLibur;
use App\Models\PeriodeJamKerja;
use App\Models\JadwalWfh;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\PeringatanKedisiplinanMail;

class SendAbsenceReminder extends Command
{
    protected $signature = 'attendance:remind {tipe}';
    protected $description = 'Kirim reminder presensi dinamis (pagi, masuk, pulang, evaluasi, siang_dosen)';

    public function handle(TelegramService $telegram)
    {
        $tipe = $this->argument('tipe');
        $validTypes = ['pagi', 'masuk', 'pulang', 'evaluasi','siang_dosen'];

        if (!in_array($tipe, $validTypes)) {
            $this->error("Tipe tidak valid! Gunakan: pagi, masuk, pulang, evaluasi, siang_dosen");
            return;
        }

        // 1. AMBIL SETTING DARI DATABASE
        $botSetting = BotSetting::first();
        if (!$botSetting) return;

        $now = Carbon::now();
        $tahun = $now->year;
        $bulanStr = $now->format('m'); 
        $hariIniStr = $now->format('d-m-Y'); 
        $tanggalDB = $now->format('Y-m-d');
        $hariKe = $now->dayOfWeekIso; 

        // 1. CEK LIBUR
        if ($hariKe > 5 || HariLibur::whereDate('tanggal', $tanggalDB)->exists()) {
            $this->info("🏝️ Hari libur/weekend. Bot istirahat.");
            return; 
        }

        // 2. TENTUKAN JAM LIMIT
        $jadwalKerja = PeriodeJamKerja::whereDate('tanggal_mulai', '<=', $tanggalDB)
            ->whereDate('tanggal_selesai', '>=', $tanggalDB)
            ->first();

        $jamMasukLimit = $jadwalKerja ? Carbon::parse($jadwalKerja->jam_masuk)->format('H:i') : '08:00';
        $batasJamPulang = ($hariKe == 5) 
            ? ($jadwalKerja ? Carbon::parse($jadwalKerja->jam_pulang_jumat)->format('H:i') : '17:00')
            : ($jadwalKerja ? Carbon::parse($jadwalKerja->jam_pulang_senin_kamis)->format('H:i') : '16:30');

        // 3. TARIK USER
        $targetRoles = ($tipe === 'siang_dosen') ? ['Dosen'] : ['Pegawai', 'Dosen'];
        $query = User::with(['roles', 'dosenDetail'])
            ->whereHas('roles', fn($q) => $q->whereIn('title', $targetRoles))
            ->whereNotNull('nip');

        if ($tipe !== 'evaluasi') {
            $query->whereNotNull('telegram_chat_id')->where('telegram_chat_id', '!=', '');
        }

        $users = $query->get();

        $this->info("🚀 Menjalankan Reminder tipe: [{$tipe}] untuk " . $users->count() . " User...");
        if ($jadwalKerja) {
            $this->info("📅 Menggunakan Periode: {$jadwalKerja->nama_periode} (Masuk: $jamMasukLimit, Pulang: $batasJamPulang)");
        } else {
            $this->info("⚠️ Menggunakan Jam Reguler Default (Masuk: $jamMasukLimit, Pulang: $batasJamPulang)");
        }

        foreach ($users as $user) {
            // Cek Keaktifan
            if ($user->roles->contains('title', 'Dosen') && ($user->dosenDetail->status_keaktifan ?? 'Aktif') !== 'Aktif') continue;

            // --- LOGIKA DETERMINASI WFH (Source of Truth Internal) ---
           $isJadwalWfh = JadwalWfh::where(function($q) use ($tanggalDB) {
                // 1. Global + Tanggal Insidental
                $q->where('is_global', true)->where('tanggal', $tanggalDB);
            })->orWhere(function($q) use ($hariKe) {
                // 2. Global + Hari Rutin
                $q->where('is_global', true)->where('hari_rutin', $hariKe);
            })->orWhere(function($q) use ($user, $tanggalDB) {
                // 3. Spesifik + Tanggal Insidental (Cek Relasi Pivot)
                $q->where('is_global', false)->where('tanggal', $tanggalDB)
                  ->whereHas('users', fn($sub) => $sub->where('users.id', $user->id));
            })->orWhere(function($q) use ($user, $hariKe) {
                // 4. Spesifik + Hari Rutin (Cek Relasi Pivot)
                $q->where('is_global', false)->where('hari_rutin', $hariKe)
                  ->whereHas('users', fn($sub) => $sub->where('users.id', $user->id));
            })->exists();

            // LOGIC A: REMINDER PAGI (Tanpa Scraping API)
            if ($tipe === 'pagi' && $botSetting->pagi_aktif) {
                $isDosen = $user->roles->contains('title', 'Dosen');
                
                if ($isDosen) {
                    $template = $isJadwalWfh ? ($botSetting->pagi_pesan_dosen_wfh ?? $botSetting->pagi_pesan_dosen) : $botSetting->pagi_pesan_dosen;
                } else {
                    $template = $isJadwalWfh ? ($botSetting->pagi_pesan_wfh ?? $botSetting->pagi_pesan) : $botSetting->pagi_pesan;
                }

                $msg = str_replace(['{nama}', '{tanggal}'], [$user->name, $hariIniStr], $template);
                $telegram->sendMessage($user->telegram_chat_id, $msg);
                $this->info("   -> Pagi (".($isJadwalWfh?'WFH':'WFO').") sent to {$user->name}");
                continue; 
            }

            // --- PROSES SCRAPING API (Untuk Masuk, Pulang, Siang, Evaluasi) ---
            if (strlen($user->nip) < 10) continue;
            $url = "https://infoabsen.unair.ac.id/absen/api_absen_8.php?nip={$user->nip}&tahun={$tahun}&bulan={$bulanStr}";

            try {
                $response = Http::timeout(15)->get($url);
                if ($response->successful()) {
                    $crawler = new Crawler($response->body());
                    $foundNode = null;
                    $crawler->filter('table tr')->each(function (Crawler $node) use ($hariIniStr, &$foundNode) {
                        if ($node->filter('td')->count() > 11) { // Ambil sampe index 11 (Status)
                            if (trim($node->filter('td')->eq(0)->text()) === $hariIniStr) {
                                $foundNode = $node; return false; 
                            }
                        }
                    });

                    $scanMasuk = '-'; $scanKeluar = '-'; $modeKerjaApi = null; $statusKehadiran = 'alpha';

                    if ($foundNode) {
                        $scanMasuk = trim($foundNode->filter('td')->eq(5)->text());
                        $scanKeluar = trim($foundNode->filter('td')->eq(8)->text());
                        $modeKerjaApi = trim($foundNode->filter('td')->eq(11)->text());

                        if (str_contains(strtolower($modeKerjaApi), 'dinas luar')) {
                            $statusKehadiran = 'dinas';
                        } elseif ($scanMasuk !== '-' && !empty($scanMasuk)) {
                            $statusKehadiran = ($scanMasuk > $jamMasukLimit) ? 'terlambat' : 'hadir';
                        }
                    }

                    // Sync Log ke DB
                    $log = AbsensiLog::updateOrCreate(
                        ['user_id' => $user->id, 'tanggal' => $tanggalDB],
                        [
                            'jam_masuk' => ($scanMasuk === '-' ? null : $scanMasuk),
                            'jam_keluar' => ($scanKeluar === '-' ? null : $scanKeluar),
                            'status' => $statusKehadiran,
                            'mode_kerja' => $modeKerjaApi,
                            'updated_at' => now(),
                        ]
                    );

                    // --- LOGIKA NOTIFIKASI BERDASARKAN MODE KERJA ---
                    $history = $log->notif_history ?? [];
                    $pesanTerkirim = false;
                    
                    // Rapikan variabel agar konsisten
                    $isDinasLuar = str_contains(strtolower($log->mode_kerja ?? ''), 'dinas luar');
                    $belumMasuk = empty($log->jam_masuk) || $log->jam_masuk === '-';

                    if ($tipe === 'siang_dosen' && $botSetting->siang_dosen_aktif) {
                        if (!empty($log->jam_masuk) && !isset($history['siang_dosen_sudah'])) {
                            $msg = str_replace(
                                ['{nama}', '{tanggal}', '{jam_masuk}'], 
                                [$user->name, $hariIniStr, $log->jam_masuk], 
                                $botSetting->siang_dosen_pesan_sudah
                            );
                            $telegram->sendMessage($user->telegram_chat_id, $msg);
                            $this->warn("   -> Info Sudah Absen (Dosen) dikirim.");
                            $history['siang_dosen_sudah'] = $now->format('H:i');
                            $pesanTerkirim = true;
                            
                        } elseif (empty($log->jam_masuk) && !isset($history['siang_dosen_belum'])) {
                            $msg = str_replace(
                                ['{nama}', '{tanggal}'], 
                                [$user->name, $hariIniStr], 
                                $botSetting->siang_dosen_pesan_belum
                            );
                            $telegram->sendMessage($user->telegram_chat_id, $msg);
                            $this->warn("   -> Peringatan Belum Absen (Dosen) dikirim.");
                            $history['siang_dosen_belum'] = $now->format('H:i');
                            $pesanTerkirim = true;
                        }
                    }

                    // LOGIC 2: MASUK
                    if ($tipe === 'masuk' && $botSetting->masuk_aktif && !$isDinasLuar) {
                        if ($belumMasuk && !isset($history['telat_masuk'])) {
                            $template = $isJadwalWfh ? ($botSetting->masuk_pesan_wfh ?? $botSetting->masuk_pesan) : $botSetting->masuk_pesan;
                            $msg = str_replace(['{nama}', '{tanggal}'], [$user->name, $hariIniStr], $template);
                            $telegram->sendMessage($user->telegram_chat_id, $msg);
                            $history['telat_masuk'] = $now->format('H:i'); 
                            $pesanTerkirim = true;
                        }
                    }

                    // LOGIC 3: SORE / PULANG
                    if ($tipe === 'pulang' && $botSetting->pulang_aktif) {
                        // Pastikan tidak dinas DAN sudah absen masuk
                        if (!$isDinasLuar && !$belumMasuk) {
                            
                            $template = $isJadwalWfh ? ($botSetting->pulang_pesan_wfh ?? $botSetting->pulang_pesan) : $botSetting->pulang_pesan;

                            if (empty($log->jam_keluar) && !isset($history['belum_pulang'])) {
                                $msg = str_replace(['{nama}', '{tanggal}', '{batas_jam}'], [$user->name, $hariIniStr, $batasJamPulang], $template);
                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $history['belum_pulang'] = $now->format('H:i'); 
                                $pesanTerkirim = true;
                            } 
                            elseif (!empty($log->jam_keluar) && $log->jam_keluar < $batasJamPulang && !isset($history['pulang_awal'])) {
                                $prefix = "⚠️ <b>PERHATIAN!</b> Sistem mencatat Anda scan keluar pukul <b>{$log->jam_keluar}</b>.\n\n";
                                $msg = $prefix . str_replace(['{nama}', '{tanggal}', '{batas_jam}'], [$user->name, $hariIniStr, $batasJamPulang], $template);
                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $this->warn("   -> Notif Pulang Awal dikirim.");
                                $history['pulang_awal'] = $now->format('H:i');
                                $pesanTerkirim = true;
                            }
                        } else {
                            // Info log di terminal agar kita tahu kenapa bot tidak ngirim
                            $alasanSkip = $isDinasLuar ? "Dinas Luar" : "Belum Absen Masuk";
                            $this->info("   -> Skip Notif Pulang (Alasan: $alasanSkip)");
                        }
                    }

                    // LOGIC 4: EVALUASI MALAM
                    if ($tipe === 'evaluasi' && $botSetting->evaluasi_aktif) {
                        if ($log->status === 'terlambat') {
                            $totalTelat = AbsensiLog::where('user_id', $user->id)
                                ->whereYear('tanggal', $tahun)
                                ->whereMonth('tanggal', $now->month) // FIX: ganti $bulan jadi $now->month
                                ->where('status', 'terlambat')
                                ->count();

                            // Evaluasi Telat 2x
                            if ($totalTelat === 2) {
                                
                                // 1. TELEGRAM (Jika Punya ID & Belum pernah dikirim)
                                if (!empty($user->telegram_chat_id) && !isset($history['telat_2x'])) {
                                    $msg = str_replace(
                                        ['{nama}', '{tanggal}'],
                                        [$user->name, $hariIniStr],
                                        $botSetting->evaluasi_pesan
                                    );
                                    $telegram->sendMessage($user->telegram_chat_id, $msg);
                                    $this->warn("   -> Notif Telat ke-2 (Telegram) dikirim.");
                                    
                                    // Set history untuk Telegram
                                    $history['telat_2x'] = $now->format('H:i');
                                    $pesanTerkirim = true;
                                }

                                // 2. EMAIL (Wajib untuk semua, Jika punya email & Belum pernah diantrekan)
                                if (!empty($user->email) && !isset($history['email_telat_2x_queued'])) {
                                    try {
                                        Mail::to($user->email)->queue(
                                            new PeringatanKedisiplinanMail(
                                                $user,
                                                $totalTelat,
                                                \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y')
                                            )
                                        );
                                        $this->warn("   -> Email Peringatan Kedisiplinan masuk antrean (queued).");
                                        
                                        // Set history terpisah untuk Email (Status: QUEUED)
                                        $history['email_telat_2x_queued'] = $now->format('H:i');
                                        $pesanTerkirim = true;
                                    } catch (\Exception $e) {
                                        $this->error("   -> Gagal antre email: " . $e->getMessage());
                                    }
                                }
                            }
                        }
                    }

                    // SIMPAN PERUBAHAN HISTORY KE DB
                    if ($pesanTerkirim) {
                        $log->notif_history = $history;
                        $log->save();
                    }

                }
            } catch (\Exception $e) {
                $this->error("   -> Error scraping: " . $e->getMessage());
            }
            
            sleep(2); 
        } 

        $this->info("✅ Proses Reminder Tipe [{$tipe}] Selesai.");
    }
}