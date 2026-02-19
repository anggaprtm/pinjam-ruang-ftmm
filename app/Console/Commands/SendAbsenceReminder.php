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
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\PeringatanKedisiplinanMail;

class SendAbsenceReminder extends Command
{
    // Tambahkan parameter {tipe} agar dinamis dipanggil dari Kernel
    protected $signature = 'attendance:remind {tipe}';
    protected $description = 'Kirim reminder presensi dinamis (pagi, masuk, pulang, evaluasi)';

    public function handle(TelegramService $telegram)
    {
        $tipe = $this->argument('tipe');
        $validTypes = ['pagi', 'masuk', 'pulang', 'evaluasi'];

        if (!in_array($tipe, $validTypes)) {
            $this->error("Tipe tidak valid! Gunakan: pagi, masuk, pulang, evaluasi");
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
        $liburNasional = HariLibur::whereDate('tanggal', $tanggalDB)->first(); // Cek di Database

        if ($isWeekend || $liburNasional) {
            // Tentukan alasan liburnya untuk dicatat di log terminal
            $alasanLibur = $liburNasional ? $liburNasional->keterangan : 'Akhir Pekan (Sabtu/Minggu)';
            
            $this->info("🏝️ Hari ini libur ({$alasanLibur}). Bot istirahat, tidak ada notifikasi yang dikirim.");
            return; // `return` akan langsung menghentikan seluruh eksekusi script di bawahnya
        }

        $batasJamPulang = ($hariKe == 5) ? '15:00' : '15:30';

        $users = User::with('roles')
            ->whereHas('roles', fn($q) => $q->where('title', 'Pegawai'))
            ->whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '!=', '')
            ->whereNotNull('nip')
            ->get();

        $this->info("🚀 Menjalankan Reminder tipe: [{$tipe}] untuk " . $users->count() . " Pegawai...");

        foreach ($users as $user) {
            $this->info("Processing: {$user->name}...");

            // ==========================================================
            // LOGIC 1: REMINDER PAGI (Tanpa Scrape)
            // ==========================================================
            if ($tipe === 'pagi') {
                if ($botSetting->pagi_aktif) {
                    $msg = str_replace(
                        ['{nama}', '{tanggal}'], 
                        [$user->name, $hariIniStr], 
                        $botSetting->pagi_pesan
                    );
                    
                    $telegram->sendMessage($user->telegram_chat_id, $msg);
                    $this->info("   -> Reminder Pagi dikirim.");
                    sleep(1); 
                }
                continue; // Lanjut ke user berikutnya, gak perlu scrape
            }

            // ==========================================================
            // START SCRAPING (Khusus masuk, pulang, dan evaluasi)
            // ==========================================================
            if (strlen($user->nip) < 10) continue;

            $url = "https://infoabsen.unair.ac.id/absen/api_absen_8.php?nip={$user->nip}&tahun={$tahun}&bulan={$bulanStr}";

            try {
                $response = Http::timeout(15)->get($url);
                
                if ($response->successful()) {
                    $crawler = new Crawler($response->body());
                    
                    // Logic Pencarian Robust
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

                        $jamMasukLimit = '08:00'; 
                        if ($scanMasuk !== '-' && !empty($scanMasuk)) {
                            $statusKehadiran = ($scanMasuk > $jamMasukLimit) ? 'terlambat' : 'hadir';
                        }
                    } else {
                        $this->warn("   -> Data tanggal $hariIniStr tidak ditemukan di tabel.");
                    }

                    // UPDATE DATABASE
                    if ($dataDitemukan) {
                        $log = AbsensiLog::updateOrCreate(
                            ['user_id' => $user->id, 'tanggal' => $tanggalDB],
                            [
                                'jam_masuk'  => ($scanMasuk === '-' || $scanMasuk === '' ? null : $scanMasuk),
                                'jam_keluar' => ($scanKeluar === '-' || $scanKeluar === '' ? null : $scanKeluar),
                                'status'     => $statusKehadiran,
                                'updated_at' => now(),
                            ]
                        );
                    } else {
                        $log = AbsensiLog::where('user_id', $user->id)->where('tanggal', $tanggalDB)->first();
                    }

                    if (!$log) {
                        continue; 
                    }

                    // --- PROSES NOTIFIKASI BERDASARKAN TIPE ---
                    $history = $log->notif_history ?? [];
                    $pesanTerkirim = false; 

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
                        // Kasus A: Belum Scan Pulang
                        if (empty($log->jam_keluar) && !isset($history['belum_pulang'])) {
                            $msg = str_replace(
                                ['{nama}', '{tanggal}', '{batas_jam}'], 
                                [$user->name, $hariIniStr, $batasJamPulang],
                                $botSetting->pulang_pesan
                            );
                            
                            $telegram->sendMessage($user->telegram_chat_id, $msg);
                            $this->warn("   -> Notif Belum Pulang dikirim.");
                            $history['belum_pulang'] = $now->format('H:i');
                            $pesanTerkirim = true;
                        } 
                        // Kasus B: Pulang Awal
                        elseif (!empty($log->jam_keluar) && $log->jam_keluar < $batasJamPulang && !isset($history['pulang_awal'])) {
                            $prefix = "⚠️ <b>PERHATIAN!</b> Sistem mencatat Anda scan keluar pukul <b>{$log->jam_keluar}.\n\n";
                            $msg = $prefix . str_replace(
                                ['{nama}', '{tanggal}', '{batas_jam}'], 
                                [$user->name, $hariIniStr, $batasJamPulang], 
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

                            // 🔴 TELAT KE-2 (TRIGGER RESMI)
                            if ($totalTelat === 2 && !isset($history['telat_2x'])) {

                                // ======================
                                // 1️⃣ TELEGRAM
                                // ======================
                                $msg = str_replace(
                                    ['{nama}', '{tanggal}'],
                                    [$user->name, $hariIniStr],
                                    $botSetting->evaluasi_pesan
                                );

                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $this->warn("   -> Notif Telat ke-2 (Telegram) dikirim.");

                                // ======================
                                // 2️⃣ EMAIL
                                // ======================
                                if (!empty($user->email)) {
                                    Mail::to($user->email)->queue(
                                        new PeringatanKedisiplinanMail(
                                            $user,
                                            $totalTelat,
                                            \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y')
                                        )
                                    );

                                    $this->warn("   -> Email Peringatan Kedisiplinan dikirim.");
                                }

                                // ======================
                                // 3️⃣ HISTORY
                                // ======================
                                $history['telat_2x'] = $now->format('Y-m-d H:i:s');
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