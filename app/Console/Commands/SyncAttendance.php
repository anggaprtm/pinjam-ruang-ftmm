<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\User;
use App\Models\AbsensiLog;
use App\Models\PeriodeJamKerja;
use App\Models\HariLibur;
use App\Models\LemburKegiatanPegawai; // ← TAMBAHAN
use Carbon\Carbon;

class SyncAttendance extends Command
{
    protected $signature = 'attendance:sync {date? : Tanggal format Y-m-d}';
    protected $description = 'Sync data presensi presisi berdasarkan tabel';

    public function handle()
    {
        // 1. Setup Tanggal
        $inputDate  = $this->argument('date') ?? date('Y-m-d');
        $targetDate = Carbon::parse($inputDate);

        $tahun    = $targetDate->year;
        $bulan    = $targetDate->format('m');
        $hariCari = $targetDate->format('d-m-Y');
        $tanggalDB = $targetDate->format('Y-m-d');

        // ==========================================================
        // 2. LOGIKA JADWAL DINAMIS
        // ==========================================================
        $jadwalKerja = PeriodeJamKerja::whereDate('tanggal_mulai', '<=', $tanggalDB)
            ->whereDate('tanggal_selesai', '>=', $tanggalDB)
            ->first();

        $isFriday = $targetDate->isFriday();

        $jamMasukLimit = $jadwalKerja
            ? Carbon::parse($jadwalKerja->jam_masuk)->format('H:i')
            : '08:00';

        if ($jadwalKerja) {
            $jamKeluarLimit = $isFriday
                ? Carbon::parse($jadwalKerja->jam_pulang_jumat)->format('H:i')
                : Carbon::parse($jadwalKerja->jam_pulang_senin_kamis)->format('H:i');
        } else {
            $jamKeluarLimit = $isFriday ? '17:00' : '16:30';
        }

        // ==========================================================
        // 3. CEK HARI LIBUR / WEEKEND (sekali, bukan per-user)
        // ==========================================================
        $isWeekend   = $targetDate->isWeekend();
        $isHariLibur = HariLibur::whereDate('tanggal', $tanggalDB)->exists();
        $isLibur     = $isWeekend || $isHariLibur;

        // ==========================================================
        // 4. LOAD DATA LEMBUR KEGIATAN HARI INI (sekali query)
        // Ini adalah daftar pegawai yang sudah diassign ke kegiatan
        // lembur di tanggal ini. Key: user_id → pivot record.
        // ==========================================================
        $assignmentHariIni = [];
        if ($isLibur) {
            $assignments = LemburKegiatanPegawai::whereHas('kegiatan', function ($q) use ($tanggalDB) {
                    $q->whereDate('tanggal', $tanggalDB);
                })
                ->get()
                ->keyBy('user_id');

            $assignmentHariIni = $assignments->toArray();
        }

        // ==========================================================
        // 5. LOOP USERS
        // ==========================================================
        $users = User::with(['roles', 'dosenDetail', 'tendikDetail'])
            ->whereHas('roles', fn($q) => $q->whereIn('title', ['Pegawai', 'Dosen']))
            ->whereNotNull('nip')
            ->get();

        $this->info("🔄 Sync Target: {$hariCari} (Bulan: $bulan)");
        if ($jadwalKerja) {
            $this->info("📅 Menggunakan Periode: {$jadwalKerja->nama_periode}");
        } else {
            $this->info("⚠️ Periode khusus tidak ditemukan. Menggunakan Jam Reguler Default.");
        }
        if ($isLibur) {
            $this->info("🏖️ Hari ini adalah hari libur/weekend. Mode lembur aktif.");
        }

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            if (strlen($user->nip) < 5) {
                $bar->advance();
                continue;
            }

            // CEK STATUS KEAKTIFAN
            $isDosen = $user->roles->contains('title', 'Dosen');
            $statusKeaktifan = $isDosen
                ? ($user->dosenDetail->status_keaktifan ?? 'Aktif')
                : ($user->tendikDetail->status_keaktifan ?? 'Aktif');

            if (strtolower($statusKeaktifan) !== 'aktif') {
                AbsensiLog::updateOrCreate(
                    ['user_id' => $user->id, 'tanggal' => $tanggalDB],
                    [
                        'jam_masuk'        => '-',
                        'jam_keluar'       => '-',
                        'batas_jam_masuk'  => $jamMasukLimit,
                        'batas_jam_keluar' => $jamKeluarLimit,
                        'status'           => strtolower($statusKeaktifan),
                        'updated_at'       => now(),
                    ]
                );
                $bar->advance();
                continue;
            }

            $url = "https://infoabsen.unair.ac.id/absen/api_absen_8.php?nip={$user->nip}&tahun={$tahun}&bulan={$bulan}";

            try {
                $response = Http::timeout(15)->get($url);

                if ($response->successful()) {
                    $crawler    = new Crawler($response->body());
                    $foundNode  = null;

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
                        $scanMasuk  = trim($foundNode->filter('td')->eq(5)->text());
                        $scanKeluar = trim($foundNode->filter('td')->eq(8)->text());

                        $scanMasuk  = ($scanMasuk  === '-' || $scanMasuk  === '') ? null : $scanMasuk;
                        $scanKeluar = ($scanKeluar === '-' || $scanKeluar === '') ? null : $scanKeluar;

                        // ──────────────────────────────────────────────
                        // LOGIKA STATUS
                        // ──────────────────────────────────────────────
                        $statusKehadiran = 'alpha';

                        if ($scanMasuk) {
                            if ($isLibur) {
                                // Hari libur/weekend: hanya 'hadir' jika durasi ≥ 4 jam
                                if ($scanMasuk && $scanKeluar) {
                                    try {
                                        $masuk  = Carbon::createFromFormat('H:i', $scanMasuk);
                                        $keluar = Carbon::createFromFormat('H:i', $scanKeluar);
                                        if ($keluar->gt($masuk) && $masuk->diffInMinutes($keluar) >= 240) {
                                            $statusKehadiran = 'hadir';
                                        }
                                    } catch (\Exception $e) { /* silent */ }
                                }
                            } else {
                                // Hari kerja normal
                                $statusKehadiran = ($scanMasuk > $jamMasukLimit) ? 'terlambat' : 'hadir';
                            }
                        }

                        // Simpan AbsensiLog
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

                        // ──────────────────────────────────────────────
                        // UPDATE STATUS VALIDASI DI PIVOT LEMBUR KEGIATAN
                        // Hanya relevan jika hari ini adalah hari libur/weekend
                        // DAN pegawai ini terdaftar di suatu kegiatan lembur.
                        // ──────────────────────────────────────────────
                        if ($isLibur && isset($assignmentHariIni[$user->id])) {
                            $statusValidasi = 'tidak_valid'; // Default: data ada tapi tidak memenuhi syarat

                            if ($statusKehadiran === 'hadir') {
                                $statusValidasi = 'valid';
                            } elseif (!$scanMasuk) {
                                // Belum ada presensi sama sekali → masih 'menunggu'
                                $statusValidasi = 'menunggu';
                            }

                            LemburKegiatanPegawai::where('lembur_kegiatan_id', $assignmentHariIni[$user->id]['lembur_kegiatan_id'])
                                ->where('user_id', $user->id)
                                ->update(['status_validasi' => $statusValidasi]);
                        }
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