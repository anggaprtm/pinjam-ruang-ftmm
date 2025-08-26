<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ruangan;
use App\Models\Kegiatan;
use Carbon\Carbon;

class HomeController
{
    public function index()
    {
        $ruanganCount = Ruangan::count();
        $kegiatanMenungguCount = Kegiatan::whereIn('status', ['belum_disetujui', 'verifikasi_sarpras', 'verifikasi_akademik'])->count();
        $kegiatanDisetujuiCount = Kegiatan::where('status', 'disetujui')->count();
        $kegiatanTotalCount = Kegiatan::count();

        // Mengambil 5 kegiatan terdekat yang akan dimulai dari sekarang
        $kegiatanTerdekat = Kegiatan::with(['user', 'ruangan'])
            ->where('waktu_mulai', '>=', Carbon::now())
            ->orderBy('waktu_mulai', 'asc')
            ->take(5)
            ->get();

        return view('home', compact(
            'ruanganCount',
            'kegiatanMenungguCount',
            'kegiatanDisetujuiCount',
            'kegiatanTotalCount',
            'kegiatanTerdekat'
        ));
    }
}
