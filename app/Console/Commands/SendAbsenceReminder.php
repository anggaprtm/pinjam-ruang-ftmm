<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Services\TelegramService;
use App\Models\User;
use App\Models\AbsensiLog;
use Carbon\Carbon;

class SendAbsenceReminder extends Command
{
    protected $signature = 'attendance:remind';
    protected $description = 'Cek presensi Info Absen (Reminder Pagi, Telat, Pulang, & Evaluasi Bulanan)';

    public function handle(TelegramService $telegram)
    {
        // Setup Waktu
        $now = Carbon::now();
        $jam  = $now->hour;
        $menit = $now->minute;
        
        $tahun = $now->year;
        $bulan = $now->month;
        $hariIniStr = $now->format('d-m-Y'); 
        $hariKe = $now->dayOfWeekIso; // 1 (Senin) - 7 (Minggu)
        
        // --- ATURAN JAM PULANG ---
        $batasJamPulang = ($hariKe == 5) ? '17:00' : '16:30';

        // Skip hari libur (Sabtu/Minggu)
        if ($hariKe > 5) {
            $this->info("Hari libur, script istirahat.");
            return;
        }

        $users = User::with('roles')
            ->whereHas('roles', fn($q) => $q->where('title', 'Pegawai'))
            ->whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '!=', '')
            ->whereNotNull('nip')
            ->get();

        $this->info("🚀 Memulai pengecekan presensi pukul {$now->format('H:i')} untuk " . $users->count() . " Pegawai...");

        foreach ($users as $user) {
            $this->info("Checking User: {$user->name}...");

            // ==========================================================
            // LOGIC 1: REMINDER PAGI (06:30) - TANPA SCRAPE
            // ==========================================================
            if ($jam == 6 && $menit >= 0 && $menit <= 59) {
                $msg = "☀️ <b>Selamat Pagi, {$user->name}!</b>\n\n" .
                       "Jangan lupa melakukan <b>Scan Masuk</b> sebelum pukul 08.00 ya.\n" .
                       "Semoga hari ini menyenangkan! 💪";
                
                $telegram->sendMessage($user->telegram_chat_id, $msg);
                $this->info("   -> Reminder 06:30 dikirim.");
                sleep(1); 
                continue; 
            }

            // ==========================================================
            // START SCRAPING (Untuk Logic 07:50, 17:00, & Evaluasi)
            // ==========================================================
            
            if (strlen($user->nip) < 10) continue;

            $url = "https://infoabsen.unair.ac.id/absen/api_absen_8.php?nip={$user->nip}&tahun={$tahun}&bulan={$bulan}";

            try {
                $response = Http::timeout(15)->get($url);
                
                if ($response->successful()) {
                    $crawler = new Crawler($response->body());
                    // Gunakan logic pencarian robust yang baru (Opsional, tapi disarankan)
                    $node = $crawler->filter('tr')->reduce(fn (Crawler $node) => str_contains($node->text(), $hariIniStr));

                    $scanMasuk = '-';
                    $scanKeluar = '-';
                    $statusKehadiran = 'alpha';

                    if ($node->count() > 0) {
                        $scanMasuk = trim($node->filter('td')->eq(5)->text());
                        $scanKeluar = trim($node->filter('td')->eq(8)->text());

                        $jamMasukLimit = '08:00'; 
                        if ($scanMasuk !== '-' && !empty($scanMasuk)) {
                            if ($scanMasuk > $jamMasukLimit) {
                                $statusKehadiran = 'terlambat';
                            } else {
                                $statusKehadiran = 'hadir';
                            }
                        }
                    }

                    // --- [MODIF 1] UPDATE DATABASE & AMBIL INSTANCE LOG ---
                    $tanggalDB = $now->format('Y-m-d');
                    
                    // Kita tampung ke variabel $log agar bisa akses kolom notif_history
                    $log = AbsensiLog::updateOrCreate(
                        ['user_id' => $user->id, 'tanggal' => $tanggalDB],
                        [
                            'jam_masuk' => ($scanMasuk === '-' ? null : $scanMasuk),
                            'jam_keluar' => ($scanKeluar === '-' ? null : $scanKeluar),
                            'status'     => $statusKehadiran,
                            'updated_at' => now(),
                        ]
                    );

                    // --- [MODIF 2] SIAPKAN VARIABEL HISTORY ---
                    // Ambil history yg sudah ada, atau array kosong jika belum ada
                    $history = $log->notif_history ?? [];
                    $pesanTerkirim = false; // Flag penanda ada update

                    // ==========================================================
                    // LOGIC 2: DEADLINE PAGI (07:50)
                    // ==========================================================
                    if ($jam == 7 && $menit >= 45) {
                        if ($scanMasuk === '-' || empty($scanMasuk)) {
                            
                            // [CEK] Apakah sudah pernah dikirim 'telat_masuk'?
                            if (!isset($history['telat_masuk'])) {
                                $msg = "🚨 <b>Peringatan Presensi Masuk</b>\n\n" .
                                       "Halo <b>{$user->name}</b>,\n" .
                                       "Sistem mendeteksi Anda belum melakukan <b>Scan Masuk</b> hari ini ($hariIniStr).\n\n" .
                                       "± 5 Menit lagi Anda bisa terlampat!!!";
                                
                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $this->warn("   -> Notif Belum Masuk dikirim.");

                                // [CATAT] Masukkan ke history
                                $history['telat_masuk'] = $now->format('H:i');
                                $pesanTerkirim = true;
                            }
                        }
                    }

                    // ==========================================================
                    // LOGIC 3: SORE (Check Jam 17:00)
                    // ==========================================================
                    if ($jam == 17) { 
                        
                        // KASUS A: Belum Scan Sama Sekali
                        if ($scanKeluar === '-' || empty($scanKeluar)) {
                            
                            // [CEK] Apakah sudah pernah dikirim 'belum_pulang'?
                            if (!isset($history['belum_pulang'])) {
                                $msg = "🔔 <b>Pengingat Presensi Pulang</b>\n\n" .
                                       "Halo <b>{$user->name}</b>,\n" .
                                       "Jam kerja telah usai ($batasJamPulang). Jangan lupa <b>Scan Keluar</b> sebelum meninggalkan kantor ya.\n\n" .
                                       "<i>Jika sedang Lembur, abaikan dan jangan lupa presensi saat pulang nanti.</i>";
                                
                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $this->warn("   -> Notif Belum Pulang dikirim.");

                                // [CATAT]
                                $history['belum_pulang'] = $now->format('H:i');
                                $pesanTerkirim = true;
                            }
                        } 
                        // KASUS B: Pulang Awal
                        elseif ($scanKeluar < $batasJamPulang) {
                            
                            // [CEK] Apakah sudah pernah dikirim 'pulang_awal'?
                            if (!isset($history['pulang_awal'])) {
                                $msg = "⚠️ <b>Pengingat Presensi Pulang</b>\n\n" .
                                       "Halo <b>{$user->name}</b>,\n" .
                                       "Sistem mencatat scan keluar Anda pukul <b>{$scanKeluar}</b>.\n" .
                                       "Jam kerja telah usai ($batasJamPulang). Jangan lupa <b>Scan Keluar</b> sebelum meninggalkan kantor ya.\n\n" .
                                       "<i>Jika sedang Lembur, abaikan dan jangan lupa presensi saat pulang nanti.</i>";

                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $this->warn("   -> Notif Pulang Awal dikirim.");

                                // [CATAT]
                                $history['pulang_awal'] = $now->format('H:i');
                                $pesanTerkirim = true;
                            }
                        }
                    }

                    // ==========================================================
                    // LOGIC 4: EVALUASI MALAM (CEK TELAT 2x)
                    // ==========================================================
                    if ($jam >= 19) {
                        if ($statusKehadiran === 'terlambat') {
                            $totalTelat = AbsensiLog::where('user_id', $user->id)
                                ->whereYear('tanggal', $tahun)
                                ->whereMonth('tanggal', $bulan)
                                ->where('status', 'terlambat')
                                ->count();
                            
                            // Hanya kirim notif PADA HARI KEDUA telat itu terjadi
                            // [CEK] Tambahan biar aman: Cek history 'telat_2x'
                            if ($totalTelat == 2 && !isset($history['telat_2x'])) {
                                $msg = "⚠️ <b>PERINGATAN KEDISIPLINAN</b>\n\n" .
                                       "Halo <b>{$user->name}</b>,\n" .
                                       "Berdasarkan rekap data presensi, Anda tercatat sudah <b>2 KALI TERLAMBAT</b> di bulan ini.\n\n" .
                                       "Mohon perhatikan jam kehadiran Anda untuk hari-hari berikutnya agar tidak terkena sanksi/potongan.\n\n" .
                                       "<i>Semangat dan usahakan besok lebih awal!</i> 🔥";
                                
                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $this->warn("   -> Notif Telat ke-2 dikirim.");

                                // [CATAT]
                                $history['telat_2x'] = $now->format('H:i');
                                $pesanTerkirim = true;
                            }
                        }
                    }

                    // --- [MODIF 3] SIMPAN PERUBAHAN HISTORY KE DB ---
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

        $this->info("✅ Selesai.");
    }
}