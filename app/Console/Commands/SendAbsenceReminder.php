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
        $bulan = $now->month; // Bulan format integer (misal: 2)
        $bulanStr = $now->format('m'); // Bulan format string (misal: "02") untuk URL jika perlu
        
        // Format Tanggal HARUS SAMA PERSIS dengan tampilan di Tabel Web (dd-mm-yyyy)
        $hariIniStr = $now->format('d-m-Y'); 
        $hariKe = $now->dayOfWeekIso; 
        
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
            // LOGIC 1: REMINDER PAGI (06:30)
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
            // START SCRAPING
            // ==========================================================
            
            if (strlen($user->nip) < 10) continue;

            // Pastikan URL pakai format bulan yang benar (gunakan $bulanStr "02" atau $bulan "2" sesuai yang jalan di Sync)
            // Sesuai screenshot sebelumnya, Sync jalan pake format $bulanStr (02)
            $url = "https://infoabsen.unair.ac.id/absen/api_absen_8.php?nip={$user->nip}&tahun={$tahun}&bulan={$bulanStr}";

            try {
                $response = Http::timeout(15)->get($url);
                
                if ($response->successful()) {
                    $crawler = new Crawler($response->body());
                    
                    // --- [FIX] LOGIC PENCARIAN ROBUST (Sama seperti SyncAttendance) ---
                    $foundNode = null;
                    $crawler->filter('table tr')->each(function (Crawler $node) use ($hariIniStr, &$foundNode) {
                        // Pastikan kolom cukup & Cek Kolom 0 (Tanggal)
                        if ($node->filter('td')->count() > 8) {
                            $tanggalDiTabel = trim($node->filter('td')->eq(0)->text());
                            if ($tanggalDiTabel === $hariIniStr) {
                                $foundNode = $node;
                                return false; // Break loop
                            }
                        }
                    });

                    // Default values
                    $scanMasuk = '-';
                    $scanKeluar = '-';
                    $statusKehadiran = 'alpha';
                    $dataDitemukan = false; // Flag penanda

                    if ($foundNode) {
                        $dataDitemukan = true;
                        $scanMasuk = trim($foundNode->filter('td')->eq(5)->text());
                        $scanKeluar = trim($foundNode->filter('td')->eq(8)->text());

                        $jamMasukLimit = '08:00'; 
                        if ($scanMasuk !== '-' && !empty($scanMasuk)) {
                            if ($scanMasuk > $jamMasukLimit) {
                                $statusKehadiran = 'terlambat';
                            } else {
                                $statusKehadiran = 'hadir';
                            }
                        }
                    } else {
                        $this->warn("   -> Data tanggal $hariIniStr tidak ditemukan di tabel.");
                    }

                    // --- [MODIF 1] UPDATE DATABASE ---
                    // HANYA UPDATE JIKA DATA DITEMUKAN DI TABEL
                    // Ini mencegah data yg sudah ada ter-overwrite jadi NULL kalau scraper gagal baca baris
                    $tanggalDB = $now->format('Y-m-d');
                    
                    if ($dataDitemukan) {
                        $log = AbsensiLog::updateOrCreate(
                            ['user_id' => $user->id, 'tanggal' => $tanggalDB],
                            [
                                'jam_masuk' => ($scanMasuk === '-' || $scanMasuk === '' ? null : $scanMasuk),
                                'jam_keluar' => ($scanKeluar === '-' || $scanKeluar === '' ? null : $scanKeluar),
                                'status'     => $statusKehadiran,
                                'updated_at' => now(),
                            ]
                        );
                    } else {
                        // Kalau data tidak ditemukan di tabel (misal format tgl beda),
                        // kita coba ambil data lama dari DB biar gak error saat akses notif_history
                        $log = AbsensiLog::where('user_id', $user->id)
                                         ->where('tanggal', $tanggalDB)
                                         ->first();
                    }

                    // Kalau log masih null (artinya scraper gagal DAN belum ada data di DB), skip user ini
                    if (!$log) {
                        $this->error("   -> Gagal proses user ini karena data kosong.");
                        continue; 
                    }

                    // --- [MODIF 2] PROSES NOTIFIKASI ---
                    $history = $log->notif_history ?? [];
                    $pesanTerkirim = false; 

                    // LOGIC 2: DEADLINE PAGI (07:50)
                    if ($jam == 7 && $menit >= 45) {
                        // Pastikan scanMasuk benar-benar kosong di DB
                        if (empty($log->jam_masuk)) { 
                            if (!isset($history['telat_masuk'])) {
                                $msg = "🚨 <b>Peringatan Presensi Masuk</b>\n\n" .
                                       "Halo <b>{$user->name}</b>,\n" .
                                       "Sistem mendeteksi Anda belum melakukan <b>Scan Masuk</b> hari ini ($hariIniStr).\n\n" .
                                       "± 5 Menit lagi Anda bisa terlampat!!!";
                                
                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $this->warn("   -> Notif Belum Masuk dikirim.");
                                $history['telat_masuk'] = $now->format('H:i');
                                $pesanTerkirim = true;
                            }
                        }
                    }

                    // LOGIC 3: SORE (17:00)
                    if ($jam == 17) { 
                        // KASUS A: Belum Scan Pulang
                        if (empty($log->jam_keluar)) {
                            if (!isset($history['belum_pulang'])) {
                                $msg = "🔔 <b>Pengingat Presensi Pulang</b>\n\n" .
                                       "Halo <b>{$user->name}</b>,\n" .
                                       "Jam kerja telah usai ($batasJamPulang). Jangan lupa <b>Scan Keluar</b> sebelum meninggalkan kantor ya.\n\n" .
                                       "<i>Jika sedang Lembur, abaikan dan jangan lupa presensi saat pulang nanti.</i>";
                                
                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $this->warn("   -> Notif Belum Pulang dikirim.");
                                $history['belum_pulang'] = $now->format('H:i');
                                $pesanTerkirim = true;
                            }
                        } 
                        // KASUS B: Pulang Awal
                        elseif ($log->jam_keluar < $batasJamPulang) {
                            if (!isset($history['pulang_awal'])) {
                                $msg = "⚠️ <b>Pengingat Presensi Pulang</b>\n\n" .
                                       "Halo <b>{$user->name}</b>,\n" .
                                       "Sistem mencatat scan keluar Anda pukul <b>{$log->jam_keluar}</b>.\n" .
                                       "Jam kerja telah usai ($batasJamPulang). Jangan lupa <b>Scan Keluar</b> sebelum meninggalkan kantor ya.\n\n" .
                                       "<i>Jika sedang Lembur, abaikan dan jangan lupa presensi saat pulang nanti.</i>";

                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $this->warn("   -> Notif Pulang Awal dikirim.");
                                $history['pulang_awal'] = $now->format('H:i');
                                $pesanTerkirim = true;
                            }
                        }
                    }

                    // LOGIC 4: EVALUASI MALAM
                    if ($jam >= 19) {
                        if ($log->status === 'terlambat') {
                            $totalTelat = AbsensiLog::where('user_id', $user->id)
                                ->whereYear('tanggal', $tahun)
                                ->whereMonth('tanggal', $bulan)
                                ->where('status', 'terlambat')
                                ->count();
                            
                            if ($totalTelat == 2 && !isset($history['telat_2x'])) {
                                $msg = "⚠️ <b>PERINGATAN KEDISIPLINAN</b>\n\n" .
                                       "Halo <b>{$user->name}</b>,\n" .
                                       "Berdasarkan rekap data presensi, Anda tercatat sudah <b>2 KALI TERLAMBAT</b> di bulan ini.\n\n" .
                                       "Mohon perhatikan jam kehadiran Anda untuk hari-hari berikutnya agar tidak terkena sanksi/potongan.\n\n" .
                                       "<i>Semangat dan usahakan besok lebih awal!</i> 🔥";
                                
                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $this->warn("   -> Notif Telat ke-2 dikirim.");
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