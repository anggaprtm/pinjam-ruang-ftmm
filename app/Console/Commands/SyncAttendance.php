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
    // UPDATE 1: Tambah parameter optional {date?}
    protected $signature = 'attendance:sync {date? : Tanggal format Y-m-d}';
    
    protected $description = 'Sync data presensi dari Info Absen (Bisa spesifik tanggal)';

    public function handle()
    {
        // UPDATE 2: Tangkap input tanggal
        $inputDate = $this->argument('date') ?? date('Y-m-d');
        $targetDate = Carbon::parse($inputDate);

        // Setup Variabel Waktu berdasarkan TANGGAL TARGET (Bukan hari ini)
        $tahun = $targetDate->year;
        $bulan = $targetDate->month;
        $hariCari = $targetDate->format('d-m-Y'); // Format text di web infoabsen (misal: 12-02-2026)
        $tanggalDB = $targetDate->format('Y-m-d'); // Format buat simpan ke DB MySQL

        // 1. Ambil User Pegawai
        $users = User::with('roles')
            ->whereHas('roles', fn($q) => $q->where('title', 'Pegawai'))
            ->whereNotNull('nip')
            ->get();

        $this->info("🔄 Sinkronisasi Presensi Tanggal: {$hariCari}");
        $this->info("👥 Total Pegawai: {$users->count()} orang...");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        // Samakan batas telat dengan notif telegram (07:31)
        $jamMasukLimit = '08:00'; 

        foreach ($users as $user) {
            
            // Skip jika NIP tidak valid
            if (strlen($user->nip) < 5) {
                $bar->advance();
                continue;
            }

            // URL scraping pake tahun & bulan dari target date
            $url = "https://infoabsen.unair.ac.id/absen/api_absen_8.php?nip={$user->nip}&tahun={$tahun}&bulan={$bulan}";

            try {
                $response = Http::timeout(10)->get($url);

                if ($response->successful()) {
                    $crawler = new Crawler($response->body());

                    // Cari baris yang mengandung tanggal target ($hariCari)
                    $node = $crawler->filter('tr')->reduce(function (Crawler $node) use ($hariCari) {
                        return str_contains($node->text(), $hariCari);
                    });

                    if ($node->count() > 0) {
                        $scanMasuk = trim($node->filter('td')->eq(5)->text());
                        $scanKeluar = trim($node->filter('td')->eq(8)->text());

                        // --- LOGIC STATUS ---
                        $statusKehadiran = 'alpha'; 

                        if ($scanMasuk !== '-' && !empty($scanMasuk)) {
                            // Bandingkan String Jam
                            if ($scanMasuk > $jamMasukLimit) {
                                $statusKehadiran = 'terlambat';
                            } else {
                                $statusKehadiran = 'hadir';
                            }
                        }

                        // --- UPDATE DATABASE ---
                        AbsensiLog::updateOrCreate(
                            [
                                'user_id' => $user->id,
                                'tanggal' => $tanggalDB // Pakai tanggal target
                            ],
                            [
                                'jam_masuk'  => ($scanMasuk === '-' ? null : $scanMasuk),
                                'jam_keluar' => ($scanKeluar === '-' ? null : $scanKeluar),
                                'status'     => $statusKehadiran,
                                'updated_at' => now(), // Menandakan kapan terakhir di-sync
                            ]
                        );
                    }
                }
            } catch (\Exception $e) {
                // Silent fail agar loop tetap jalan
            }

            // Jeda sopan
            usleep(200000); // 0.2 detik cukup
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Sinkronisasi Selesai!");
    }
}