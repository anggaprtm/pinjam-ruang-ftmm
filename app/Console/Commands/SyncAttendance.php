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
    protected $signature = 'attendance:sync {date? : Tanggal format Y-m-d}';
    protected $description = 'Sync data presensi presisi berdasarkan tabel';

    public function handle()
    {
        // 1. Setup Tanggal
        $inputDate = $this->argument('date') ?? date('Y-m-d');
        $targetDate = Carbon::parse($inputDate);

        // Format URL (misal: 2026, 02)
        $tahun = $targetDate->year;
        $bulan = $targetDate->format('m'); 

        // Format Pencarian di Tabel (Sesuai Screenshot: 02-02-2026)
        $hariCari = $targetDate->format('d-m-Y');
        $tanggalDB = $targetDate->format('Y-m-d'); 

        $users = User::with('roles')
            ->whereHas('roles', fn($q) => $q->where('title', 'Pegawai'))
            ->whereNotNull('nip')
            ->get();

        $this->info("🔄 Sync Target: {$hariCari} (Bulan: $bulan)");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $jamMasukLimit = '08:00'; 

        foreach ($users as $user) {
            if (strlen($user->nip) < 5) {
                $bar->advance();
                continue;
            }

            $url = "https://infoabsen.unair.ac.id/absen/api_absen_8.php?nip={$user->nip}&tahun={$tahun}&bulan={$bulan}";

            try {
                $response = Http::timeout(15)->get($url);

                if ($response->successful()) {
                    $crawler = new Crawler($response->body());

                    // --- LOGIC BARU: LOOPING BARIS TABEL ---
                    // Kita cari manual baris yg kolom pertamanya == $hariCari
                    $foundNode = null;

                    $crawler->filter('table tr')->each(function (Crawler $node) use ($hariCari, &$foundNode) {
                        // Pastikan baris ini punya minimal 9 kolom (biar gak error pas ambil index 8)
                        if ($node->filter('td')->count() > 8) {
                            // Ambil teks di kolom pertama (Tanggal)
                            $tanggalDiTabel = trim($node->filter('td')->eq(0)->text());
                            
                            // Bandingkan persis!
                            if ($tanggalDiTabel === $hariCari) {
                                $foundNode = $node;
                                return false; // Stop looping (break)
                            }
                        }
                    });

                    if ($foundNode) {
                        // Ambil Data (Index 5 & 8 sesuai screenshot)
                        $scanMasuk = trim($foundNode->filter('td')->eq(5)->text());
                        $scanKeluar = trim($foundNode->filter('td')->eq(8)->text());

                        // Bersihkan data jika isinya cuma strip "-" atau kosong
                        $scanMasuk = ($scanMasuk === '-' || $scanMasuk === '') ? null : $scanMasuk;
                        $scanKeluar = ($scanKeluar === '-' || $scanKeluar === '') ? null : $scanKeluar;

                        // Tentukan Status
                        $statusKehadiran = 'alpha';
                        
                        // Jika ada scan masuk
                        if ($scanMasuk) {
                            $statusKehadiran = ($scanMasuk > $jamMasukLimit) ? 'terlambat' : 'hadir';
                        }
                        // Jika Hari Libur (biasanya baris ada, tapi isinya - semua)
                        // Cek kolom "Keterangan" atau "Status" (Index 11) jika perlu, tapi logic di atas cukup.

                        AbsensiLog::updateOrCreate(
                            ['user_id' => $user->id, 'tanggal' => $tanggalDB],
                            [
                                'jam_masuk'  => $scanMasuk,
                                'jam_keluar' => $scanKeluar,
                                'status'     => $statusKehadiran,
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            } catch (\Exception $e) {
                // Silent fail
            }
            
            usleep(100000); // Jeda 0.1 detik
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Selesai.");
    }
}