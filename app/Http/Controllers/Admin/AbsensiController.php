<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', Carbon::now()->format('Y-m-d'));

        // 1. Ambil Data
        $logs = AbsensiLog::with('user')
            ->whereDate('tanggal', $tanggal)
            ->orderBy('status', 'desc')
            ->get();

        $lastSync = AbsensiLog::whereDate('tanggal', $tanggal)->max('updated_at');

        // 2. Tentukan Batas Pulang Berdasarkan Tanggal yang Difilter
        $carbonDate = Carbon::parse($tanggal);
        
        // Cek apakah hari Jumat?
        if ($carbonDate->isFriday()) {
            $batasPulang = '17:00';
        } else {
            $batasPulang = '16:30'; // Senin - Kamis (dan Weekend jika ada lembur)
        }

        // 3. Hitung Statistik Pakai Batas Dinamis
        $pulangAwalCount = $logs->filter(function($log) use ($batasPulang) {
            return !empty($log->jam_keluar) && $log->jam_keluar !== '-' && $log->jam_keluar < $batasPulang;
        })->count();

        $stats = [
            'total_pegawai' => \App\Models\User::whereHas('roles', fn($q) => $q->where('title', 'Pegawai'))->count(),
            'hadir'         => $logs->where('status', 'hadir')->count(),
            'terlambat'     => $logs->where('status', 'terlambat')->count(),
            'alpha'         => $logs->where('status', 'alpha')->count(),
            'pulang_awal'   => $pulangAwalCount,
        ];

        return view('admin.absensi.index', compact('logs', 'stats', 'tanggal', 'lastSync', 'batasPulang'));
    }
}