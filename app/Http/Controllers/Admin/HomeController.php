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

        $jadwalHariIniText = "*JADWAL PEMAKAIAN RUANGAN FTMM - " . Carbon::today()->translatedFormat('l, d F Y') . "*\n\n";
        if ($kegiatanHariIni->isEmpty()) {
            $jadwalHariIniText .= "Tidak ada kegiatan terjadwal untuk hari ini.";
        } else {
            $kegiatanByRuangan = $kegiatanHariIni->groupBy('ruangan.nama');

            foreach ($kegiatanByRuangan as $namaRuangan => $kegiatans) {
                $jadwalHariIniText .= "*{$namaRuangan}*\n";
                foreach ($kegiatans as $kegiatan) {
                    $waktuMulai = Carbon::parse($kegiatan->waktu_mulai)->format('H:i');
                    $waktuSelesai = Carbon::parse($kegiatan->waktu_selesai)->format('H:i');
                    $namaKegiatan = $kegiatan->nama_kegiatan;
                    $pic = $kegiatan->nama_pic ? " (PIC: {$kegiatan->nama_pic})" : "";
                    $jadwalHariIniText .= "- {$waktuMulai} - {$waktuSelesai}: {$namaKegiatan} [{$kegiatan->user->name}] {$pic}\n";
                }
                $jadwalHariIniText .= "\n"; 
            }
        }

        $jadwalBesokText = "*JADWAL PEMAKAIAN RUANGAN FTMM - " . Carbon::tomorrow()->translatedFormat('l, d F Y') . "*\n\n";
        if ($kegiatanBesok->isEmpty()) {
            $jadwalBesokText .= "Tidak ada kegiatan terjadwal untuk besok.";
        } else {
            foreach ($kegiatanBesok->groupBy('ruangan.nama') as $namaRuangan => $kegiatans) {
                $jadwalBesokText .= "*{$namaRuangan}*\n";
                foreach ($kegiatans as $kegiatan) {
                    $waktuMulai = Carbon::parse($kegiatan->waktu_mulai)->format('H:i');
                    $waktuSelesai = Carbon::parse($kegiatan->waktu_selesai)->format('H:i');
                    $pic = $kegiatan->nama_pic ? " (PIC: {$kegiatan->nama_pic})" : "";
                    $jadwalBesokText .= "- {$waktuMulai} - {$waktuSelesai}: {$kegiatan->nama_kegiatan} [{$kegiatan->user->name}] {$pic}\n";
                }
                $jadwalBesokText .= "\n";
            }
        }

        $footerText = "_Disalin dari Aplikasi PINJAM-RUANG FTMM ✨_";
        $jadwalHariIniText .= $footerText;
        $jadwalBesokText .= $footerText;

        return view('home', compact(
            'ruanganCount',
            'kegiatanMenungguCount',
            'kegiatanDisetujuiCount',
            'kegiatanTotalCount',
            'kegiatanHariIni',
            'kegiatanBesok',
            'jadwalHariIniText',
            'jadwalBesokText'
        ));
    }
}
