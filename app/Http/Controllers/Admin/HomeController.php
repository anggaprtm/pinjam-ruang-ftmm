<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ruangan;
use App\Models\Kegiatan;
use Carbon\Carbon;

class HomeController
{
    public function index()
    {
        // Data untuk kartu statistik
        $ruanganCount = Ruangan::count();
        $kegiatanMenungguCount = Kegiatan::whereIn('status', ['belum_disetujui', 'verifikasi_sarpras', 'verifikasi_akademik'])->count();
        $kegiatanDisetujuiCount = Kegiatan::where('status', 'disetujui')->count();
        $kegiatanTotalCount = Kegiatan::count();

        // Data untuk tabel tab
        $kegiatanHariIni = Kegiatan::with(['user', 'ruangan'])
            ->whereDate('waktu_mulai', Carbon::today())
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        $kegiatanBesok = Kegiatan::with(['user', 'ruangan'])
            ->whereDate('waktu_mulai', Carbon::tomorrow())
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        return view('home', compact(
            'ruanganCount',
            'kegiatanMenungguCount',
            'kegiatanDisetujuiCount',
            'kegiatanTotalCount',
            'kegiatanHariIni',
            'kegiatanBesok'
        ));
    }
}
