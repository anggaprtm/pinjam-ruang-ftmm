<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalPerkuliahan;
use App\Models\Kegiatan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SignageController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        $today = Carbon::now();
        $todayName = $today->translatedFormat('l');

        // Ambil Filter URL
        $filterLantai = $request->query('lantai');
        $filterGedung = $request->query('gedung');

        // ==========================================
        // 1. QUERY JADWAL KULIAH
        // ==========================================
        $jadwalQuery = JadwalPerkuliahan::where('hari', $todayName)
            ->whereDate('berlaku_mulai', '<=', $today)
            ->whereDate('berlaku_sampai', '>=', $today)
            ->with(['ruangan']);

        // Filter Lantai & Gedung untuk Kuliah
        if ($filterLantai) {
            $jadwalQuery->whereHas('ruangan', function($q) use ($filterLantai) {
                $q->where('lantai', 'LIKE', "%{$filterLantai}%");
            });
        }
        if ($filterGedung) {
            $jadwalQuery->whereHas('ruangan', function($q) use ($filterGedung) {
                $q->where('gedung', 'LIKE', "%{$filterGedung}%");
            });
        }

        $jadwalKuliah = $jadwalQuery->orderBy('waktu_mulai')->get()->map(function ($jadwal) {
            return [
                'title' => $jadwal->mata_kuliah,
                'course_code' => $jadwal->kode_matkul ?? substr($jadwal->program_studi, 0, 2).'-'.$jadwal->id,
                'time' => Carbon::parse($jadwal->waktu_mulai)->format('H:i') . ' - ' . Carbon::parse($jadwal->waktu_selesai)->format('H:i'),
                'room' => $jadwal->ruangan->nama ?? '-',
                'pic' => $jadwal->dosen ?? '-',
                'type' => 'kuliah',
            ];
        });

        // ==========================================
        // 2. QUERY KEGIATAN (EVENTS) - FIX FILTER DISINI
        // ==========================================
        $kegiatanQuery = Kegiatan::where('status', 'disetujui')
            ->whereDate('waktu_mulai', '>=', $today)
            ->with(['ruangan', 'user']);

        // --- FILTER LANTAI DIPASANG DISINI JUGA ---
        if ($filterLantai) {
            $kegiatanQuery->whereHas('ruangan', function($q) use ($filterLantai) {
                $q->where('lantai', 'LIKE', "%{$filterLantai}%");
            });
        }
        // --- FILTER GEDUNG DIPASANG DISINI JUGA ---
        if ($filterGedung) {
            $kegiatanQuery->whereHas('ruangan', function($q) use ($filterGedung) {
                $q->where('gedung', 'LIKE', "%{$filterGedung}%");
            });
        }

        $kegiatan = $kegiatanQuery->orderBy('waktu_mulai')->limit(5)->get()->map(function ($kegiatan) {
            $startTime = Carbon::parse($kegiatan->waktu_mulai);
            $endTime = Carbon::parse($kegiatan->waktu_selesai);
            $timeString = $startTime->format('H:i') . ' - ' . $endTime->format('H:i');

            return [
                'id' => $kegiatan->id,
                'title' => $kegiatan->nama_kegiatan,
                'time' => $timeString,
                'location' => $kegiatan->ruangan->nama ?? 'TBA',
                'speaker' => $kegiatan->user->name ?? $kegiatan->nama_pic,
                
                // Data tambahan untuk UI Events Panel Baru
                'date_day' => $startTime->format('d'),       // Tanggal (20)
                'date_month' => $startTime->translatedFormat('M'), // Bulan (Okt)
                'category' => 'Kegiatan Ormawa',
                // Gambar placeholder unik berdasarkan ID kegiatan
                'image' => 'https://picsum.photos/seed/event' . $kegiatan->id . '/800/600', 
                'type' => 'kegiatan',
            ];
        });

        return response()->json([
            'jadwal_kuliah_hari_ini' => $jadwalKuliah,
            'kegiatan_mendatang' => $kegiatan,
        ]);
    }
}