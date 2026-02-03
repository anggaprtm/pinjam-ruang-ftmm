<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Services\TelegramService;
use App\Models\User;

class SendAbsenceReminder extends Command
{
    protected $signature = 'attendance:remind';
    protected $description = 'Cek presensi Info Absen (Khusus Pegawai)';

    public function handle(TelegramService $telegram)
    {
        // 1. FILTER USER
        // - Punya ID Telegram
        // - Punya NIP
        // - Punya Role "Pegawai"
        
        $users = User::with('roles') // Eager load biar ringan
            ->whereHas('roles', function ($query) {
                $query->where('title', 'Pegawai'); 
            })
            ->whereNotNull('telegram_chat_id')
            ->where('telegram_chat_id', '!=', '')
            ->whereNotNull('nip')
            ->get();

        $this->info("🚀 Memulai pengecekan presensi untuk " . $users->count() . " Pegawai...");

        if ($users->isEmpty()) {
            $this->warn("Tidak ada pegawai yang memenuhi syarat (Telegram ID & NIP).");
            return;
        }

        $tahun = date('Y');
        $bulan = date('m');
        $hariIni = date('d-m-Y');
        $hariKe = date('N');
        $batasJamPulang = ($hariKe == 5) ? '17:00' : '16:30';
        $batasTelatMasuk = '08:00';

        
        $this->info("📅 Hari ke-$hariKe. Batas Jam Pulang: $batasJamPulang");

        foreach ($users as $user) {
            $this->info("Checking Pegawai: {$user->name}...");

            // Skip jika NIP terlalu pendek (bukan format infoabsen) - Opsional
            if (strlen($user->nip) < 10) {
                $this->warn("   -> NIP tidak valid/pendek. Skip.");
                continue;
            }

            $url = "https://infoabsen.unair.ac.id/absen/api_absen_8.php?nip={$user->nip}&tahun={$tahun}&bulan={$bulan}";

            try {
                $response = Http::timeout(10)->get($url);

                if ($response->successful()) {
                    $crawler = new Crawler($response->body());

                    // Cari baris tanggal hari ini
                    $node = $crawler->filter('tr')->reduce(function (Crawler $node) use ($hariIni) {
                        return str_contains($node->text(), $hariIni);
                    });

                    if ($node->count() > 0) {
                        $scanMasuk = trim($node->filter('td')->eq(5)->text());
                        $scanKeluar = trim($node->filter('td')->eq(8)->text());

                        // --- LOGIC PENENTUAN STATUS ---
                        $statusKehadiran = 'alpha';
                        
                        // Konversi jam masuk string ke format waktu untuk perbandingan
                        // Asumsi jam masuk maksimal 08:00 (Sesuaikan aturan kampus)
                        $jamMasukLimit = '08:00'; 

                        if ($scanMasuk !== '-' && !empty($scanMasuk)) {
                            if ($scanMasuk <= $jamMasukLimit) {
                                $statusKehadiran = 'hadir'; // Tepat waktu
                            } else {
                                $statusKehadiran = 'terlambat'; // Telat
                            }
                        }

                        // --- SIMPAN KE DATABASE LOKAL ---
                        // Konversi tanggal scraper (02-02-2026) ke Format MySQL (2026-02-02)
                        $tanggalDB = \Carbon\Carbon::createFromFormat('d-m-Y', $hariIni)->format('Y-m-d');

                        \App\Models\AbsensiLog::updateOrCreate(
                            [
                                'user_id' => $user->id,
                                'tanggal' => $tanggalDB
                            ],
                            [
                                'jam_masuk' => ($scanMasuk === '-' ? null : $scanMasuk),
                                'jam_keluar' => ($scanKeluar === '-' ? null : $scanKeluar),
                                'status'     => $statusKehadiran,
                                'updated_at' => now(),
                            ]
                        );

                        

                        // --- SKENARIO 1: BELUM MASUK ---
                        if ($scanMasuk === '-' || empty($scanMasuk)) {
                            $msg = "🚨 <b>Peringatan Presensi Masuk</b>\n\n" .
                                   "Halo <b>{$user->name}</b>,\n" .
                                   "Sistem mendeteksi Anda belum melakukan <b>Scan Masuk</b> hari ini ($hariIni).\n\n" .
                                   "Jangan lupa melakukan presensi!!";
                            
                            $telegram->sendMessage($user->telegram_chat_id, $msg);
                            $this->warn("   -> Notif Masuk dikirim.");
                            
                            sleep(1); 
                            continue; // Lanjut ke user lain
                        }

                        // --- SKENARIO 2: CEK PULANG ---
                        // Jalankan hanya jika waktu SEKARANG sudah melewati batas jam pulang
                        if (date('H:i') >= $batasJamPulang) {
                            
                            // A. Belum Scan Sama Sekali
                            if ($scanKeluar === '-' || empty($scanKeluar)) {
                                $msg = "🔔 <b>Pengingat Presensi Pulang</b>\n\n" .
                                       "Halo <b>{$user->name}</b>,\n" .
                                       "Jam kerja hari ini (hingga $batasJamPulang) telah usai.\n" .
                                       "Jangan lupa melakukan <b>Scan Keluar</b> sebelum meninggalkan kantor.\n\n" .
                                       "<i>Jika sedang Lembur, abaikan dan scan saat pulang nanti.</i>";
                                
                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $this->warn("   -> Notif Belum Pulang dikirim.");
                            } 
                            // B. Pulang Awal
                            elseif ($scanKeluar < $batasJamPulang) {
                                $msg = "⚠️ <b>Pengingat Presensi Pulang</b>\n\n" .
                                       "Halo <b>{$user->name}</b>,\n" .
                                       "Sistem mencatat scan keluar Anda pukul <b>{$scanKeluar}</b>.\n\n" .
                                       "• Jangan lupa melakukan <b>Scan Keluar</b> sebelum meninggalkan kantor.\n" .
                                       "• <b>Jika Anda sedang LEMBUR</b>, Semangat!";

                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $this->warn("   -> Notif Pulang Awal dikirim.");
                            }
                            // C. PULANG AMAN (Masuk sini jika A dan B lolos)
                            else {
                                // C.1: Pagi Telat?
                                if ($scanMasuk > $batasTelatMasuk) {
                                    $msg = "🌙 <b>Rekap Presensi: Telat Masuk</b>\n\n" .
                                           "Halo <b>{$user->name}</b>, presensi pulang Anda aman ({$scanKeluar}).\n\n" .
                                           "📝 <b>Catatan:</b> Pagi ini Anda masuk pukul <b>{$scanMasuk}</b> (Lewat {$batasTelatMasuk}).\n" .
                                           "Besok usahakan berangkat lebih awal ya supaya tidak terlambat. Semangat! 💪";
                                    
                                    $telegram->sendMessage($user->telegram_chat_id, $msg);
                                    $this->warn("   -> Notif Pagi telat dikirim.");
                                } 
                                // C.2: Pagi Aman & Pulang Aman (PERFECT)
                                else {
                                    $msg = "🌟 <b>Presensi Hari Ini: AMAN</b>\n\n" .
                                           "Halo <b>{$user->name}</b>,\n" .
                                           "Terima kasih, data presensi Anda hari ini <b>LENGKAP & TEPAT WAKTU</b>.\n" .
                                           "✅ Masuk: {$scanMasuk}\n" .
                                           "✅ Pulang: {$scanKeluar}\n\n" .
                                           "Selamat beristirahat, sampai jumpa besok! 👋" ;
                                            
                                    
                                    $telegram->sendMessage($user->telegram_chat_id, $msg);
                                    $this->warn("   -> Notif AMAN dikirim.");
                                }
                            } // End Else C
                            
                        } // End
                    

                    } else {
                                
                            // C.1: Pagi Telat?
                            if ($scanMasuk > $batasTelatMasuk) {
                                $msg = "🌙 <b>Rekap Presensi: Telat Masuk</b>\n\n" .
                                        "Halo <b>{$user->name}</b>, presensi pulang Anda aman ({$scanKeluar}).\n\n" .
                                        "📝 <b>Catatan:</b> Pagi ini Anda masuk pukul <b>{$scanMasuk}</b> (Lewat {$batasTelatMasuk}).\n" .
                                        "Besok usahakan berangkat lebih awal ya supaya tidak terlambat. Semangat! 💪";
                                
                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $this->warn("   -> Notif Pagi telat dikirim.");
                            } 
                            // C.2: Pagi Aman & Pulang Aman (PERFECT)
                            else {
                                $msg = "🌟 <b>Presensi Hari Ini: AMAN</b>\n\n" .
                                        "Halo <b>{$user->name}</b>,\n" .
                                        "Terima kasih, data presensi Anda hari ini <b>LENGKAP & TEPAT WAKTU</b>.\n" .
                                        "✅ Masuk: {$scanMasuk}\n" .
                                        "✅ Pulang: {$scanKeluar}\n\n" .
                                        "Selamat beristirahat, sampai jumpa besok! 👋";
                                
                                $telegram->sendMessage($user->telegram_chat_id, $msg);
                                $this->warn("   -> Notif AMAN dikirim.");
                            }
                        }
                }
            } catch (\Exception $e) {
                $this->error("   -> Error scraping: " . $e->getMessage());
            }

            sleep(2); // Jeda sopan
        }

        $this->info("✅ Selesai.");
    }
}