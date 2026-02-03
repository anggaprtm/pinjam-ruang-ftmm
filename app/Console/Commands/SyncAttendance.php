<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\User;
use App\Models\AbsensiLog;
use Carbon\Carbon;

class SyncAttendance extends Command
{
    // Nama command: attendance:sync
    protected $signature = 'attendance:sync';
    protected $description = 'Sync data presensi dari Info Absen ke Database lokal (Tanpa Notif)';

    public function handle()
    {
        // 1. Ambil User Pegawai yang punya NIP
        $users = User::with('roles')
            ->whereHas('roles', fn($q) => $q->where('title', 'Pegawai'))
            ->whereNotNull('nip')
            ->get();

        $this->info("🔄 Memulai Sinkronisasi Data Presensi ({$users->count()} Pegawai)...");

        // Progress Bar biar keren kalau dijalankan manual
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $tahun = date('Y');
        $bulan = date('m');
        $hariIni = date('d-m-Y'); 
        $tanggalDB = Carbon::now()->format('Y-m-d'); // Format buat simpan ke DB

        // Batas Jam Masuk (Untuk penentuan status Hadir/Terlambat)
        $jamMasukLimit = '08:00'; 

        foreach ($users as $user) {
            
            // Skip jika NIP kependekan (invalid)
            if (strlen($user->nip) < 5) {
                $bar->advance();
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

                        // --- LOGIC TENTUKAN STATUS (Hadir/Terlambat/Alpha) ---
                        // Default status
                        $statusKehadiran = 'alpha'; 

                        if ($scanMasuk !== '-' && !empty($scanMasuk)) {
                            // Bandingkan String Jam: "07:55" <= "08:00"
                            if ($scanMasuk <= $jamMasukLimit) {
                                $statusKehadiran = 'hadir'; // Tepat Waktu
                            } else {
                                $statusKehadiran = 'terlambat'; // Telat
                            }
                        }

                        // --- UPDATE DATABASE LOKAL ---
                        AbsensiLog::updateOrCreate(
                            [
                                'user_id' => $user->id,
                                'tanggal' => $tanggalDB
                            ],
                            [
                                'jam_masuk'  => ($scanMasuk === '-' ? null : $scanMasuk),
                                'jam_keluar' => ($scanKeluar === '-' ? null : $scanKeluar),
                                'status'     => $statusKehadiran,
                                'updated_at' => now(), // Update timestamp biar ketahuan "Last Sync"
                            ]
                        );
                    }
                }
            } catch (\Exception $e) {
                // Silent fail aja, jangan stop loop
                // Log::error("Gagal sync user {$user->name}: " . $e->getMessage());
            }

            // Jeda dikit biar server sana gak keberatan
            usleep(500000); // 0.5 detik
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Sinkronisasi Selesai!");
    }
}