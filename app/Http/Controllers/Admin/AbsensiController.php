<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        // Default tanggal hari ini
        $tanggal = $request->input('tanggal', Carbon::now()->format('Y-m-d'));
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        // 1. DATA UTAMA: Ambil Semua Pegawai + Data Absen Tanggal Terpilih
        // Kita pakai "with" dengan kondisi tanggal biar user tetap muncul walau belum ada log
        $pegawais = User::whereHas('roles', function($q) {
                $q->where('title', 'Pegawai');
            })
            ->with(['absensiLogs' => function($query) use ($tanggal) {
                $query->whereDate('tanggal', $tanggal);
            }])
            ->orderBy('name', 'asc') // Urutkan nama A-Z
            ->get();

        // 2. LEADERBOARD TELAT (Bulan Berjalan)
        // Menghitung siapa yang paling sering 'terlambat' bulan ini
        $topLate = AbsensiLog::selectRaw('user_id, count(*) as total_telat, GROUP_CONCAT(DAY(tanggal) ORDER BY tanggal SEPARATOR ",") as tanggal_telat')
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->where('status', 'terlambat')
            ->groupBy('user_id')
            ->orderByDesc('total_telat') // Paling banyak telat di atas
            ->with('user')
            ->limit(5) // Ambil Top 5
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
        $batasPulang = $carbonDate->isFriday() ? '17:00' : '16:30';

        foreach ($pegawais as $pegawai) {
            $log = $pegawai->absensiLogs->first(); // Data log hari ini (bisa null)

            if (!$log) {
                $stats['alpha']++;
            } else {
                if ($log->status == 'hadir') $stats['hadir']++;
                if ($log->status == 'terlambat') $stats['terlambat']++;
                
                // Cek Pulang Awal
                if (!empty($log->jam_keluar) && $log->jam_keluar !== '-' && $log->jam_keluar < $batasPulang) {
                    $stats['pulang_awal']++;
                }
            }
        }

        // Info Last Sync
        $lastSync = AbsensiLog::whereDate('tanggal', $tanggal)->max('updated_at');

        return view('admin.absensi.index', compact(
            'pegawais', 'stats', 'topLate', 'tanggal', 'lastSync', 'batasPulang'
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