<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiLog;
use App\Models\User;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        // Default tanggal hari ini
        $tanggal = $request->input('tanggal', Carbon::now()->format('Y-m-d'));
        $roleFilter = $request->input('role', 'Pegawai');
        $carbonDate = Carbon::parse($tanggal);
        
        $bulanIni = $carbonDate->month;
        $tahunIni = $carbonDate->year;
        // ==========================================================
        // 1. LOGIKA CEK HARI LIBUR
        // ==========================================================
        
        // Cek Libur di Database (Libur Nasional / Cuti Bersama)
        $dataLibur = HariLibur::whereDate('tanggal', $tanggal)->first();
        
        // Cek Weekend (Sabtu/Minggu)
        $isWeekend = $carbonDate->isWeekend();

        // Gabungkan Logic: Libur jika (Weekend) ATAU (Ada di Database)
        $isLibur = $isWeekend || $dataLibur;

        // Siapkan Teks Keterangan Libur untuk di View
        $keteranganLibur = '-';
        if ($dataLibur) {
            $keteranganLibur = $dataLibur->keterangan; // Prioritas utama: Nama Libur Nasional
        } elseif ($isWeekend) {
            $keteranganLibur = 'Weekend (Sabtu/Minggu)';
        }

        // ==========================================================
        // 2. DATA UTAMA: Ambil Pegawai (Dengan Filter Libur)
        // ==========================================================
        $query = User::whereHas('roles', function($q) {
            $q->where('title', 'Pegawai');
        });

        // KONDISI LIBUR:
        // Jika libur, HANYA ambil pegawai yang melakukan presensi (Lembur).
        // Yang tidak ada jam masuk/keluar tidak akan ditarik datanya.
        $query = User::whereHas('roles', function($q) use ($roleFilter) {
            $q->where('title', $roleFilter); // Filter berdasarkan role yang dipilih
        });

        if ($isLibur) {
            $query->whereHas('absensiLogs', function($q) use ($tanggal) {
                $q->whereDate('tanggal', $tanggal)
                  ->where(function($sub) {
                      $sub->whereNotNull('jam_masuk')
                          ->orWhereNotNull('jam_keluar');
                  });
            });
        }

        // Eksekusi query dengan meload relasi absensi hari tersebut
        $pegawais = $query->with(['absensiLogs' => function($query) use ($tanggal) {
                $query->whereDate('tanggal', $tanggal);
            }])
            ->orderBy('name', 'asc') // Urutkan nama A-Z
            ->get();

        // 2. LEADERBOARD TELAT (Bulan Berjalan)
        // Menghitung siapa yang paling sering 'terlambat' bulan ini
        $statusTarget = ($roleFilter === 'Dosen') ? 'alpha' : 'terlambat';

        $topStats = AbsensiLog::selectRaw('user_id, count(*) as total_kasus, GROUP_CONCAT(DAY(tanggal) ORDER BY tanggal SEPARATOR ",") as tanggal_kasus')
            ->whereHas('user.roles', function($q) use ($roleFilter) {
                $q->where('title', $roleFilter);
            })
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->where('status', $statusTarget)
            ->groupBy('user_id')
            ->orderByDesc('total_kasus') 
            ->with('user')
            ->limit(5) 
            ->get();

        // 3. STATISTIK REALTIME (Hitung dari collection $pegawais)
        // Kita cek manual dari collection di atas biar hemat query
        $stats = [
            'total_pegawai' => $pegawais->count(),
            'hadir'         => 0,
            'terlambat'     => 0,
            'alpha'         => 0,
            'pulang_awal'   => 0
        ];

        // Tentukan Batas Pulang
        $carbonDate = Carbon::parse($tanggal);
        $batasPulang = $carbonDate->isFriday() ? '15:00' : '15:30';

        foreach ($pegawais as $pegawai) {
            $log = $pegawai->absensiLogs->first(); // Data log hari ini (bisa null)

            if (!$log || $log->status === 'alpha') {
                $stats['alpha']++;
            } else {
                if ($log->status == 'hadir') $stats['hadir']++;
                if ($log->status == 'terlambat') $stats['terlambat']++;
                
                // Cek Pulang Awal
                if ($roleFilter === 'Pegawai' && !empty($log->jam_keluar) && $log->jam_keluar !== '-' && $log->jam_keluar < $batasPulang) {
                    $stats['pulang_awal']++;
                }
            }
        }

        // Info Last Sync
        $lastSync = AbsensiLog::whereDate('tanggal', $tanggal)->max('updated_at');

        return view('admin.absensi.index', compact(
            'pegawais', 'stats', 'topStats', 'tanggal', 'lastSync', 'batasPulang',
            'isLibur', 'keteranganLibur', 'roleFilter'
        ));
    }

    public function sync(Request $request)
    {
        // Tangkap tanggal dari input hidden di form
        $tanggalTarget = $request->input('tanggal', date('Y-m-d'));

        try {
            // Panggil command dengan argumen tanggal
            // Syntax: Artisan::call('command', ['argument' => value]);
            Artisan::call('attendance:sync', ['date' => $tanggalTarget]);

            return back()->with('message', 'Sinkronisasi data tanggal ' . $tanggalTarget . ' selesai!');
        } catch (\Exception $e) {
            return back()->withErrors('Gagal sinkronisasi: ' . $e->getMessage());
        }
    }
}