<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\User;
use App\Models\AbsensiLog;
use App\Models\PeriodeJamKerja;
use App\Models\HariLibur;
use App\Models\LemburKegiatanPegawai;
use Carbon\Carbon;

class SyncAttendance extends Command
{
    protected $signature = 'attendance:sync {date? : Tanggal format Y-m-d}';
    protected $description = 'Sync data presensi presisi berdasarkan tabel (concurrent mode)';

    public function handle()
    {
        $startTime = microtime(true);

        // ==========================================================
        // 1. SETUP TANGGAL
        // ==========================================================
        $inputDate  = $this->argument('date') ?? date('Y-m-d');
        $targetDate = Carbon::parse($inputDate);

        $tahun     = $targetDate->year;
        $bulan     = $targetDate->format('m');
        $hariCari  = $targetDate->format('d-m-Y');
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
        // 3. CEK HARI LIBUR / WEEKEND
        // ==========================================================
        $isWeekend   = $targetDate->isWeekend();
        $isHariLibur = HariLibur::whereDate('tanggal', $tanggalDB)->exists();
        $isLibur     = $isWeekend || $isHariLibur;
        $sudahLewat  = !$targetDate->isToday();

        // ==========================================================
        // 4. LOAD DATA LEMBUR KEGIATAN HARI INI (sekali query)
        // ==========================================================
        $assignmentHariIni = [];
        if ($isLibur) {
            $assignmentHariIni = LemburKegiatanPegawai::whereHas('kegiatan', function ($q) use ($tanggalDB) {
                    $q->whereDate('tanggal', $tanggalDB);
                })
                ->get()
                ->keyBy('user_id')
                ->toArray();
        }

        // ==========================================================
        // 5. LOAD SEMUA USER
        // ==========================================================
        $users = User::with(['roles', 'dosenDetail', 'tendikDetail'])
            ->whereHas('roles', fn($q) => $q->whereIn('title', ['Pegawai', 'Dosen']))
            ->whereNotNull('nip')
            ->get();

        $this->info("🔄 Sync Target: {$hariCari} (Bulan: {$bulan})");
        $this->info("👥 Total user ditemukan: {$users->count()}");

        if ($jadwalKerja) {
            $this->info("📅 Menggunakan Periode: {$jadwalKerja->nama_periode}");
        } else {
            $this->info("⚠️  Periode khusus tidak ditemukan. Menggunakan Jam Reguler Default.");
        }
        if ($isLibur) {
            $this->info("🏖️  Hari ini adalah hari libur/weekend. Mode lembur aktif.");
        }

        // ==========================================================
        // 6. PISAH USER: NON-AKTIF vs AKTIF
        //    Non-aktif → langsung bulk upsert, skip HTTP request.
        //    Aktif     → masuk pool concurrent.
        // ==========================================================
        $logsNonAktif = [];   // Akan di-bulk-upsert langsung
        $usersAktif   = [];   // Akan di-fetch via pool

        foreach ($users as $user) {
            // Skip NIP terlalu pendek
            if (strlen($user->nip) < 5) continue;

            $isDosen = $user->roles->contains('title', 'Dosen');
            $statusKeaktifan = $isDosen
                ? ($user->dosenDetail->status_keaktifan ?? 'Aktif')
                : ($user->tendikDetail->status_keaktifan ?? 'Aktif');

            if (strtolower($statusKeaktifan) !== 'aktif') {
                $logsNonAktif[] = [
                    'user_id'          => $user->id,
                    'tanggal'          => $tanggalDB,
                    'jam_masuk'        => '-',
                    'jam_keluar'       => '-',
                    'batas_jam_masuk'  => $jamMasukLimit,
                    'batas_jam_keluar' => $jamKeluarLimit,
                    'status'           => strtolower($statusKeaktifan),
                    'mode_kerja'       => '-', // <-- TAMBAHKAN INI
                    'updated_at'       => now(),
                    'created_at'       => now(),
                ];
            } else {
                $usersAktif[] = $user;
            }
        }

        // Bulk upsert user non-aktif (satu query)
        if (!empty($logsNonAktif)) {
            AbsensiLog::upsert(
                $logsNonAktif,
                ['user_id', 'tanggal'],
                ['jam_masuk', 'jam_keluar', 'batas_jam_masuk', 'batas_jam_keluar', 'status', 'mode_kerja', 'updated_at']
            );
            $this->info("💤 " . count($logsNonAktif) . " user non-aktif di-skip (bulk upsert).");
        }

        if (empty($usersAktif)) {
            $this->info("✅ Tidak ada user aktif untuk di-fetch.");
            return;
        }

        // ==========================================================
        // 7. CONCURRENT HTTP REQUESTS via Http::pool()
        // ==========================================================
        $this->info("🌐 Mengirim " . count($usersAktif) . " request secara paralel...");

        $responses = Http::pool(function ($pool) use ($usersAktif, $tahun, $bulan) {
            foreach ($usersAktif as $user) {
                $pool->as((string) $user->id)
                    ->timeout(20)
                    ->get("https://infoabsen.unair.ac.id/absen/api_absen_8.php", [
                        'nip'   => $user->nip,
                        'tahun' => $tahun,
                        'bulan' => $bulan,
                    ]);
            }
        });

        $this->info("✅ Semua response diterima. Memproses data...");

        // ==========================================================
        // 8. PROSES RESPONSE & KUMPULKAN DATA
        // ==========================================================
        $logsToUpsert        = [];
        $lemburToUpdate      = []; // ['lembur_kegiatan_id' => X, 'user_id' => Y, 'status' => Z]
        $countSuccess        = 0;
        $countFailed         = 0;

        $bar = $this->output->createProgressBar(count($usersAktif));
        $bar->start();

        foreach ($usersAktif as $user) {
            $response = $responses[(string) $user->id] ?? null;

            // Jika response gagal / exception dari pool
            if (!$response || $response instanceof \Throwable || !$response->successful()) {
                $countFailed++;
                $bar->advance();
                continue;
            }

            try {
                $crawler   = new Crawler($response->body());
                $foundNode = null;

                $crawler->filter('table tr')->each(function (Crawler $node) use ($hariCari, &$foundNode) {
                    if ($node->filter('td')->count() > 11) { // Pastikan kolom minimal ada 12 (index 0-11)
                        $tanggalDiTabel = trim($node->filter('td')->eq(0)->text());
                        if ($tanggalDiTabel === $hariCari) {
                            $foundNode = $node;
                            return false; // Berhenti looping jika tanggal ketemu
                        }
                    }
                });

                if (!$foundNode) {
                    $bar->advance();
                    continue;
                }

                // AMBIL DATA DARI TABEL API
                $scanMasuk    = trim($foundNode->filter('td')->eq(5)->text());
                $scanKeluar   = trim($foundNode->filter('td')->eq(8)->text());
                $modeKerjaRaw = trim($foundNode->filter('td')->eq(11)->text()); // <-- Index ke-11 sesuai struktur API

                $scanMasuk  = ($scanMasuk  === '-' || $scanMasuk  === '') ? null : $scanMasuk;
                $scanKeluar = ($scanKeluar === '-' || $scanKeluar === '') ? null : $scanKeluar;

                // ──────────────────────────────────────────────
                // LOGIKA STATUS KEHADIRAN & MODE KERJA
                // ──────────────────────────────────────────────
                $statusKehadiran = 'alpha';
                $modeKerjaStr = strtolower($modeKerjaRaw);

                // 1. Tangani khusus Dinas Luar (Scan masuk/keluar biasanya kosong)
                if (str_contains($modeKerjaStr, 'dinas luar')) {
                    $statusKehadiran = 'dinas'; 
                } 
                // 2. Jika bukan Dinas Luar dan ada Scan Masuk
                elseif ($scanMasuk) {
                    if ($isLibur) {
                        // Hari libur: 'hadir' hanya jika durasi ≥ 4 jam
                        if ($scanKeluar) {
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

                // Kumpulkan untuk bulk upsert
                $logsToUpsert[] = [
                    'user_id'          => $user->id,
                    'tanggal'          => $tanggalDB,
                    'jam_masuk'        => $scanMasuk,
                    'jam_keluar'       => $scanKeluar,
                    'batas_jam_masuk'  => $jamMasukLimit,
                    'batas_jam_keluar' => $jamKeluarLimit,
                    'status'           => $statusKehadiran,
                    'mode_kerja'       => $modeKerjaRaw, // Simpan keterangan mode kerja asli dari API
                    'updated_at'       => now(),
                    'created_at'       => now(),
                ];

                // ──────────────────────────────────────────────
                // KUMPULKAN UPDATE STATUS VALIDASI LEMBUR
                // ──────────────────────────────────────────────
                if ($isLibur && isset($assignmentHariIni[$user->id])) {
                    if ($statusKehadiran === 'hadir') {
                        $statusValidasi = 'valid';
                    } elseif (!$scanMasuk) {
                        $statusValidasi = $sudahLewat ? 'tidak_fr' : 'menunggu';
                    } elseif (!$scanKeluar) {
                        $statusValidasi = $sudahLewat ? 'tidak_valid' : 'menunggu';
                    } else {
                        $statusValidasi = 'tidak_valid';
                    }

                    $lemburToUpdate[] = [
                        'lembur_kegiatan_id' => $assignmentHariIni[$user->id]['lembur_kegiatan_id'],
                        'user_id'            => $user->id,
                        'status_validasi'    => $statusValidasi,
                    ];
                }

                $countSuccess++;
            } catch (\Exception $e) {
                $countFailed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // ==========================================================
        // 9. BULK UPSERT ABSENSI LOG (satu query untuk semua)
        // ==========================================================
        if (!empty($logsToUpsert)) {
            AbsensiLog::upsert(
                $logsToUpsert,
                ['user_id', 'tanggal'],
                ['jam_masuk', 'jam_keluar', 'batas_jam_masuk', 'batas_jam_keluar', 'status', 'mode_kerja', 'updated_at']
            );
            $this->info("💾 " . count($logsToUpsert) . " AbsensiLog berhasil di-upsert.");
        }

        // ==========================================================
        // 10. UPDATE STATUS VALIDASI LEMBUR
        // ==========================================================
        if (!empty($lemburToUpdate)) {
            foreach ($lemburToUpdate as $item) {
                LemburKegiatanPegawai::where('lembur_kegiatan_id', $item['lembur_kegiatan_id'])
                    ->where('user_id', $item['user_id'])
                    ->update(['status_validasi' => $item['status_validasi']]);
            }
            $this->info("📋 " . count($lemburToUpdate) . " status validasi lembur diperbarui.");
        }

        // ==========================================================
        // 11. SUMMARY
        // ==========================================================
        $elapsed = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info("──────────────────────────────────────");
        $this->info("✅ Selesai dalam {$elapsed} detik.");
        $this->info("   • Berhasil   : {$countSuccess} user");
        $this->info("   • Gagal/Skip : {$countFailed} user");
        $this->info("   • Non-aktif  : " . count($logsNonAktif) . " user");
        $this->info("──────────────────────────────────────");
    }
}