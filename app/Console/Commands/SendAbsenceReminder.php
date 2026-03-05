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
use App\Models\PeriodeJamKerja; // Tambahkan import model ini
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
        if (!$botSetting) {
            $this->error("Setting bot belum diatur di database (Tabel bot_settings kosong).");
            return;
        }

        $now = Carbon::now();
        $tahun = $now->year;
        $bulan = $now->month; 
        $bulanStr = $now->format('m'); 
        $hariIniStr = $now->format('d-m-Y'); 
        $tanggalDB = $now->format('Y-m-d');
        $hariKe = $now->dayOfWeekIso; 

        $isWeekend = $hariKe > 5; // Sabtu = 6, Minggu = 7
        $liburNasional = HariLibur::whereDate('tanggal', $tanggalDB)->first(); 

        if ($isWeekend || $liburNasional) {
            $alasanLibur = $liburNasional ? $liburNasional->keterangan : 'Akhir Pekan (Sabtu/Minggu)';
            $this->info("🏝️ Hari ini libur ({$alasanLibur}). Bot istirahat, tidak ada notifikasi yang dikirim.");
            return; 
        }

        // ==========================================================
        // LOGIC JADWAL DINAMIS (PENENTUAN BATAS JAM)
        // ==========================================================
        $jadwalKerja = PeriodeJamKerja::whereDate('tanggal_mulai', '<=', $tanggalDB)
            ->whereDate('tanggal_selesai', '>=', $tanggalDB)
            ->first();

        $isFriday = ($hariKe == 5);

        // Tentukan Batas Masuk
        $jamMasukLimit = $jadwalKerja ? \Carbon\Carbon::parse($jadwalKerja->jam_masuk)->format('H:i') : '08:00';

        // Tentukan Batas Pulang
        if ($jadwalKerja) {
            $batasJamPulang = $isFriday 
                ? \Carbon\Carbon::parse($jadwalKerja->jam_pulang_jumat)->format('H:i') 
                : \Carbon\Carbon::parse($jadwalKerja->jam_pulang_senin_kamis)->format('H:i');
        } else {
            // Fallback jam reguler
            $batasJamPulang = $isFriday ? '17:00' : '16:30'; 
        }

        $targetRoles = ['Pegawai']; // Default
        
        if ($tipe === 'pagi') {
            $targetRoles = ['Pegawai', 'Dosen']; // Pagi untuk semuanya
        } elseif ($tipe === 'siang_dosen') {
            $targetRoles = ['Dosen']; // Siang eksklusif untuk dosen
        }

        $users = User::with(['roles', 'dosenDetail']) // Tambahkan relasi dosenDetail
            ->whereHas('roles', fn($q) => $q->whereIn('title', $targetRoles))
            ->whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '!=', '')
            ->whereNotNull('nip')
            ->get();

        $this->info("🚀 Menjalankan Reminder tipe: [{$tipe}] untuk " . $users->count() . " User...");
        if ($jadwalKerja) {
            $this->info("📅 Menggunakan Periode: {$jadwalKerja->nama_periode} (Masuk: $jamMasukLimit, Pulang: $batasJamPulang)");
        } else {
            $this->info("⚠️ Menggunakan Jam Reguler Default (Masuk: $jamMasukLimit, Pulang: $batasJamPulang)");
        }

        foreach ($users as $user) {
            $this->info("Processing: {$user->name}...");

            $isDosen = $user->roles->contains('title', 'Dosen');
            if ($isDosen) {
                $statusKeaktifan = $user->dosenDetail->status_keaktifan ?? 'Aktif';
                if ($statusKeaktifan !== 'Aktif') {
                    $this->warn("   -> Di-skip (Status: {$statusKeaktifan})");
                    continue; 
                }
            }

            // LOGIC 1: REMINDER PAGI
            if ($tipe === 'pagi') {
                if ($botSetting->pagi_aktif) {
                    
                    // 1. Cek apakah user ini punya role "Dosen"
                    $isDosen = $user->roles->contains('title', 'Dosen');

                    // 2. Tentukan template pesan yang mau dipakai
                    // Kalau Dosen, pakai pagi_pesan_dosen. Kalau di DB kosong, fallback ke pagi_pesan biasa.
                    // Kalau bukan Dosen (Tendik), langsung pakai pagi_pesan biasa.
                    $templatePesan = $isDosen 
                        ? ($botSetting->pagi_pesan_dosen ?? $botSetting->pagi_pesan) 
                        : $botSetting->pagi_pesan;

                    // 3. Masukkan template yang sudah dipilih ke str_replace
                    $msg = str_replace(
                        ['{nama}', '{tanggal}'], 
                        [$user->name, $hariIniStr], 
                        $templatePesan // <-- Pakai variabel ini, jangan $botSetting->pagi_pesan langsung
                    );
                    
                    $telegram->sendMessage($user->telegram_chat_id, $msg);
                    
                    // Opsional: Biar di terminal kelihatan jelas bot ngirim template yang mana
                    $roleLabel = $isDosen ? 'Dosen' : 'Tendik';
                    $this->info("   -> Reminder Pagi ($roleLabel) dikirim.");
                    
                    sleep(1); 
                }
                continue; 
            }

            // START SCRAPING
            if (strlen($user->nip) < 10) continue;

            $url = "https://infoabsen.unair.ac.id/absen/api_absen_8.php?nip={$user->nip}&tahun={$tahun}&bulan={$bulanStr}";

            try {
                $response = Http::timeout(15)->get($url);
                
                if ($response->successful()) {
                    $crawler = new Crawler($response->body());
                    
                    $foundNode = null;
                    $crawler->filter('table tr')->each(function (Crawler $node) use ($hariIniStr, &$foundNode) {
                        if ($node->filter('td')->count() > 8) {
                            $tanggalDiTabel = trim($node->filter('td')->eq(0)->text());
                            if ($tanggalDiTabel === $hariIniStr) {
                                $foundNode = $node;
                                return false; 
                            }
                        }
                    });

                    $scanMasuk = '-';
                    $scanKeluar = '-';
                    $statusKehadiran = 'alpha';
                    $dataDitemukan = false;

                    if ($foundNode) {
                        $dataDitemukan = true;
                        $scanMasuk = trim($foundNode->filter('td')->eq(5)->text());
                        $scanKeluar = trim($foundNode->filter('td')->eq(8)->text());

                        if ($scanMasuk !== '-' && !empty($scanMasuk)) {
                            $statusKehadiran = ($scanMasuk > $jamMasukLimit) ? 'terlambat' : 'hadir';
                        }
                    } else {
                        $this->warn("   -> Data tanggal $hariIniStr tidak ditemukan di tabel.");
                    }

                    // UPDATE DATABASE (Sekarang menyimpan snapshot batas jam)
                    if ($dataDitemukan) {
                        $log = AbsensiLog::updateOrCreate(
                            ['user_id' => $user->id, 'tanggal' => $tanggalDB],
                            [
                                'jam_masuk'        => ($scanMasuk === '-' || $scanMasuk === '' ? null : $scanMasuk),
                                'jam_keluar'       => ($scanKeluar === '-' || $scanKeluar === '' ? null : $scanKeluar),
                                'batas_jam_masuk'  => $jamMasukLimit,
                                'batas_jam_keluar' => $batasJamPulang,
                                'status'           => $statusKehadiran,
                                'updated_at'       => now(),
                            ]
                        );
                    } else {
                        $log = AbsensiLog::where('user_id', $user->id)->where('tanggal', $tanggalDB)->first();
                    }

                    if (!$log) {
                        continue; 
                    }

                    // PROSES NOTIFIKASI
                    $history = $log->notif_history ?? [];
                    $pesanTerkirim = false; 

                    if ($tipe === 'siang_dosen' && $botSetting->siang_dosen_aktif) {
                        if (!empty($log->jam_masuk) && !isset($history['siang_dosen_sudah'])) {
                            // Jika sudah absen masuk
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
                            // Jika belum absen masuk
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

                    // LOGIC 2: PERINGATAN MASUK
                    if ($tipe === 'masuk' && $botSetting->masuk_aktif) {
                        if (empty($log->jam_masuk) && !isset($history['telat_masuk'])) {
                            $msg = str_replace(
                                ['{nama}', '{tanggal}'], 
                                [$user->name, $hariIniStr], 
                                $botSetting->masuk_pesan
                            );
                            
                            $telegram->sendMessage($user->telegram_chat_id, $msg);
                            $this->warn("   -> Notif Belum Masuk dikirim.");
                            $history['telat_masuk'] = $now->format('H:i');
                            $pesanTerkirim = true;
                        }
                    }

                    // LOGIC 3: SORE / PULANG
                    if ($tipe === 'pulang' && $botSetting->pulang_aktif) { 
                        if (empty($log->jam_keluar) && !isset($history['belum_pulang'])) {
                            $msg = str_replace(
                                ['{nama}', '{tanggal}', '{batas_jam}'], 
                                [$user->name, $hariIniStr, $batasJamPulang], // Variabel dinamis
                                $botSetting->pulang_pesan
                            );
                            
                            $telegram->sendMessage($user->telegram_chat_id, $msg);
                            $this->warn("   -> Notif Belum Pulang dikirim.");
                            $history['belum_pulang'] = $now->format('H:i');
                            $pesanTerkirim = true;
                        } 
                        elseif (!empty($log->jam_keluar) && $log->jam_keluar < $batasJamPulang && !isset($history['pulang_awal'])) {
                            $prefix = "⚠️ <b>PERHATIAN!</b> Sistem mencatat Anda scan keluar pukul <b>{$log->jam_keluar}</b>.\n\n";
                            $msg = $prefix . str_replace(
                                ['{nama}', '{tanggal}', '{batas_jam}'], 
                                [$user->name, $hariIniStr, $batasJamPulang], // Variabel dinamis
                                $botSetting->pulang_pesan
                            );

                            $telegram->sendMessage($user->telegram_chat_id, $msg);
                            $this->warn("   -> Notif Pulang Awal dikirim.");
                            $history['pulang_awal'] = $now->format('H:i');
                            $pesanTerkirim = true;
                        }
                    }

                    // LOGIC 4: EVALUASI MALAM
                    if ($tipe === 'evaluasi' && $botSetting->evaluasi_aktif) {
                        if ($log->status === 'terlambat') {
                            $totalTelat = AbsensiLog::where('user_id', $user->id)
                                ->whereYear('tanggal', $tahun)
                                ->whereMonth('tanggal', $bulan)
                                ->where('status', 'terlambat')
                                ->count();

                            // Evaluasi Telat 2x
                            if ($totalTelat === 2 && !isset($history['telat_2x'])) {
                                // 1. TELEGRAM
                                $msg = str_replace(
                                    ['{nama}', '{tanggal}'],
                                    [$user->name, $hariIniStr],
                                    $botSetting->evaluasi_pesan
                                );
                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $this->warn("   -> Notif Telat ke-2 (Telegram) dikirim.");
                                
                                // Set history untuk Telegram
                                $history['telat_2x'] = $now->format('H:i');

                                // 2. EMAIL
                                if (!empty($user->email)) {
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
                                    } catch (\Exception $e) {
                                        $this->error("   -> Gagal antre email: " . $e->getMessage());
                                    }
                                }

                                // 3. HISTORY
                                $pesanTerkirim = true;
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