<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiLog;
use App\Models\User;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('presensi_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
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
            }, 'dosenDetail'])
            ->orderBy('name', 'asc')
            ->get();

        // 2. LEADERBOARD TELAT (Bulan Berjalan)
        $statusTarget = ($roleFilter === 'Dosen') ? 'alpha' : 'terlambat';

        // Ambil array tanggal libur nasional bulan ini
        $liburDates = HariLibur::whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->pluck('tanggal')->toArray();

        $topStats = AbsensiLog::selectRaw('user_id, count(*) as total_kasus, GROUP_CONCAT(DAY(tanggal) ORDER BY tanggal SEPARATOR ",") as tanggal_kasus')
            ->whereHas('user.roles', function($q) use ($roleFilter) {
                $q->where('title', $roleFilter);
            })
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->where('status', $statusTarget)
            
            // EXCLUDE WEEKEND (1 = Minggu, 7 = Sabtu di MySQL)
            ->whereRaw('DAYOFWEEK(tanggal) NOT IN (1, 7)')
            
            // EXCLUDE HARI LIBUR NASIONAL
            ->when(count($liburDates) > 0, function($query) use ($liburDates) {
                $query->whereNotIn('tanggal', $liburDates);
            })
            
            ->groupBy('user_id')
            ->orderByDesc('total_kasus') 
            ->with('user')
            ->limit(5) 
            ->get();

        // 3. LEADERBOARD LEMBUR (Bulan Berjalan)
        // Hitung status 'hadir' yang terjadi di akhir pekan atau hari libur nasional
        $topLembur = AbsensiLog::selectRaw('user_id, count(*) as total_lembur, GROUP_CONCAT(DAY(tanggal) ORDER BY tanggal SEPARATOR ",") as tanggal_lembur')
            ->whereHas('user.roles', function($q) use ($roleFilter) {
                $q->where('title', $roleFilter);
            })
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->where('status', 'hadir')
            ->where(function($query) use ($liburDates) {
                // Syarat lembur: Weekend (1=Minggu, 7=Sabtu) ATAU ada di tabel hari libur
                $query->whereRaw('DAYOFWEEK(tanggal) IN (1, 7)');
                if (count($liburDates) > 0) {
                    $query->orWhereIn('tanggal', $liburDates);
                }
            })
            ->groupBy('user_id')
            ->orderByDesc('total_lembur')
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
            
            // Default alpha jika belum ada log sama sekali
            $statusLog = $log->status ?? 'alpha';

            if ($statusLog === 'alpha') {
                $stats['alpha']++;
            } elseif ($statusLog === 'hadir') {
                $stats['hadir']++;
            } elseif ($statusLog === 'terlambat') {
                $stats['terlambat']++;
            } 
            // Jika statusnya 'cuti' atau 'tugas belajar', dia tidak dihitung ke statistik manapun (atau bisa kamu buat variabel stat baru jika mau)

            // Cek Pulang Awal (Pastikan BUKAN hari libur, dan statusnya masuk kerja normal)
            if (!$isLibur && $roleFilter === 'Pegawai' && in_array($statusLog, ['hadir', 'terlambat']) && !empty($log->jam_keluar) && $log->jam_keluar !== '-' && $log->jam_keluar < $batasPulang) {
                $stats['pulang_awal']++;
            }
        }

        // Info Last Sync
        $lastSync = AbsensiLog::whereDate('tanggal', $tanggal)->max('updated_at');

        return view('admin.absensi.index', compact(
            'pegawais', 'stats', 'topStats', 'topLembur', 'tanggal', 'lastSync', 'batasPulang',
            'isLibur', 'keteranganLibur', 'roleFilter'
        ));
    }

    public function rekapTelat(Request $request)
    {
        // Default bulan ini jika tidak ada parameter
        $bulanParam = $request->input('bulan', Carbon::now()->format('Y-m'));
        $roleFilter = $request->input('role', 'Pegawai');
        
        $parsedDate = Carbon::createFromFormat('Y-m', $bulanParam);
        $bulanIni = $parsedDate->month;
        $tahunIni = $parsedDate->year;

        $statusTarget = ($roleFilter === 'Dosen') ? 'alpha' : 'terlambat';

        $liburDates = HariLibur::whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->pluck('tanggal')->toArray();

        $rekaps = AbsensiLog::selectRaw('user_id, count(*) as total_kasus, GROUP_CONCAT(DAY(tanggal) ORDER BY tanggal SEPARATOR ", ") as tanggal_kasus')
            ->whereHas('user.roles', function($q) use ($roleFilter) {
                $q->where('title', $roleFilter);
            })
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->where('status', $statusTarget)
            ->whereRaw('DAYOFWEEK(tanggal) NOT IN (1, 7)') // Exclude weekend
            ->when(count($liburDates) > 0, function($query) use ($liburDates) {
                $query->whereNotIn('tanggal', $liburDates); // Exclude libur nasional
            })
            ->groupBy('user_id')
            ->orderByDesc('total_kasus')
            ->with('user')
            ->get(); // Tanpa limit agar tampil semua

        return view('admin.absensi.rekap-telat', compact('rekaps', 'bulanParam', 'roleFilter'));
    }

    public function rekapLembur(Request $request)
    {
        $bulanParam = $request->input('bulan', Carbon::now()->format('Y-m'));
        $roleFilter = $request->input('role', 'Pegawai');
        
        $parsedDate = Carbon::createFromFormat('Y-m', $bulanParam);
        $bulanIni = $parsedDate->month;
        $tahunIni = $parsedDate->year;

        $liburDates = HariLibur::whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->pluck('tanggal')->toArray();

        $rekaps = AbsensiLog::selectRaw('user_id, count(*) as total_lembur, GROUP_CONCAT(DAY(tanggal) ORDER BY tanggal SEPARATOR ", ") as tanggal_kasus')
            ->whereHas('user.roles', function($q) use ($roleFilter) {
                $q->where('title', $roleFilter);
            })
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->where('status', 'hadir') // Syaratnya hadir
            ->where(function($query) use ($liburDates) {
                // Saat weekend atau libur nasional
                $query->whereRaw('DAYOFWEEK(tanggal) IN (1, 7)');
                if (count($liburDates) > 0) {
                    $query->orWhereIn('tanggal', $liburDates);
                }
            })
            ->groupBy('user_id')
            ->orderByDesc('total_lembur')
            ->with('user')
            ->get();

        return view('admin.absensi.rekap-lembur', compact('rekaps', 'bulanParam', 'roleFilter'));
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