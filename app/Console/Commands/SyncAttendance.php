<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\User;
use App\Models\AbsensiLog;
use App\Models\PeriodeJamKerja; 
use App\Models\HariLibur;
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

        // Format Pencarian di Tabel
        $hariCari = $targetDate->format('d-m-Y');
        $tanggalDB = $targetDate->format('Y-m-d'); 

        // ==========================================================
        // 2. LOGIKA JADWAL DINAMIS (PENENTUAN BATAS JAM)
        // ==========================================================
        // Cari jadwal aktif di rentang tanggal sinkronisasi
        $jadwalKerja = PeriodeJamKerja::whereDate('tanggal_mulai', '<=', $tanggalDB)
            ->whereDate('tanggal_selesai', '>=', $tanggalDB)
            ->first();

        $isFriday = $targetDate->isFriday();

        // Jika jadwal di DB ditemukan, pakai jadwal tersebut.
        // JIKA TIDAK (belum diset), pakai nilai fallback di bawah ini:
        $jamMasukLimit = $jadwalKerja ? \Carbon\Carbon::parse($jadwalKerja->jam_masuk)->format('H:i') : '08:00';

        if ($jadwalKerja) {
            $jamKeluarLimit = $isFriday 
                ? \Carbon\Carbon::parse($jadwalKerja->jam_pulang_jumat)->format('H:i') 
                : \Carbon\Carbon::parse($jadwalKerja->jam_pulang_senin_kamis)->format('H:i');
        } else {
            // FALLBACK REGULER (Senin-Kamis 16:30, Jumat 17:00)
            $jamKeluarLimit = $isFriday ? '17:00' : '16:30'; 
        }

        $users = User::with(['roles', 'dosenDetail'])
            ->whereHas('roles', fn($q) => $q->whereIn('title', ['Pegawai', 'Dosen']))
            ->whereNotNull('nip')
            ->get();

        $this->info("🔄 Sync Target: {$hariCari} (Bulan: $bulan)");
        if ($jadwalKerja) {
            $this->info("📅 Menggunakan Periode: {$jadwalKerja->nama_periode}");
        } else {
            $this->info("⚠️ Periode khusus tidak ditemukan. Menggunakan Jam Reguler Default.");
        }
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            if (strlen($user->nip) < 5) {
                $bar->advance();
                continue;
            }

            $isDosen = $user->roles->contains('title', 'Dosen');
            if ($isDosen) {
                $statusKeaktifan = $user->dosenDetail->status_keaktifan ?? 'Aktif';
                if ($statusKeaktifan !== 'Aktif') {
                    // Skip sinkronisasi untuk dosen ini
                    $bar->advance();
                    continue; 
                }
            }

            $url = "https://infoabsen.unair.ac.id/absen/api_absen_8.php?nip={$user->nip}&tahun={$tahun}&bulan={$bulan}";

            try {
                $response = Http::timeout(15)->get($url);

                if ($response->successful()) {
                    $crawler = new Crawler($response->body());
                    $foundNode = null;

                    $crawler->filter('table tr')->each(function (Crawler $node) use ($hariCari, &$foundNode) {
                        if ($node->filter('td')->count() > 8) {
                            $tanggalDiTabel = trim($node->filter('td')->eq(0)->text());
                            if ($tanggalDiTabel === $hariCari) {
                                $foundNode = $node;
                                return false; 
                            }
                        }
                    });

                    if ($foundNode) {
                        $scanMasuk = trim($foundNode->filter('td')->eq(5)->text());
                        $scanKeluar = trim($foundNode->filter('td')->eq(8)->text());

                        $scanMasuk = ($scanMasuk === '-' || $scanMasuk === '') ? null : $scanMasuk;
                        $scanKeluar = ($scanKeluar === '-' || $scanKeluar === '') ? null : $scanKeluar;

                        // ==========================================================
                        // CEK HARI LIBUR / WEEKEND
                        // ==========================================================
                        $isWeekend = $targetDate->isWeekend();
                        $isHariLibur = HariLibur::whereDate('tanggal', $tanggalDB)->exists();
                        $isLibur = $isWeekend || $isHariLibur;

                        // Menentukan status kehadiran
                        $statusKehadiran = 'alpha';

                        if ($scanMasuk) {
                            if ($isLibur) {
                                // Syarat Lembur Valid: Harus ada jam masuk, jam keluar, dan minimal 4 jam
                                if ($scanMasuk && $scanKeluar) {
                                    try {
                                        $masuk = \Carbon\Carbon::createFromFormat('H:i', $scanMasuk);
                                        $keluar = \Carbon\Carbon::createFromFormat('H:i', $scanKeluar);
                                        
                                        if ($keluar->greaterThan($masuk)) {
                                            $durasiMenit = $masuk->diffInMinutes($keluar);
                                            if ($durasiMenit >= 240) { // 240 menit = 4 jam
                                                $statusKehadiran = 'hadir';
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        // Fallback aman jika API nge-return format jam yang aneh
                                    }
                                }
                                // Catatan: Jika belum scan keluar ATAU durasi < 4 jam, status tetap 'alpha'.
                                // Tapi data jam_masuk & jam_keluar tetap tersimpan di database.
                                
                            } else {
                                // Hari kerja normal
                                $statusKehadiran = ($scanMasuk > $jamMasukLimit) ? 'terlambat' : 'hadir';
                            }
                        }

                        // SIMPAN SNAPSHOT BATAS JAM KE DALAM LOG
                        AbsensiLog::updateOrCreate(
                            ['user_id' => $user->id, 'tanggal' => $tanggalDB],
                            [
                                'jam_masuk'        => $scanMasuk,
                                'jam_keluar'       => $scanKeluar,
                                'batas_jam_masuk'  => $jamMasukLimit,
                                'batas_jam_keluar' => $jamKeluarLimit,
                                'status'           => $statusKehadiran,
                                'updated_at'       => now(),
                            ]
                        );
                    }
                }
            } catch (\Exception $e) {
                // Silent fail
            }
            
            usleep(100000); 
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Selesai.");
    }
}