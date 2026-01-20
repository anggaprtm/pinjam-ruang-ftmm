<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalPerkuliahan;
use App\Models\Kegiatan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SignageController extends Controller
{
    public function index()
    {
        Carbon::setLocale('id');
        $today = Carbon::now();
        $todayName = $today->translatedFormat('l');

        // Jadwal Kuliah Hari Ini
        $jadwalKuliah = JadwalPerkuliahan::where('hari', $todayName)
            ->whereDate('berlaku_mulai', '<=', $today)
            ->whereDate('berlaku_sampai', '>=', $today)
            ->with('ruangan')
            ->orderBy('waktu_mulai')
            ->get()
            ->map(function ($jadwal) {
                return [
                    'title' => $jadwal->mata_kuliah,
                    'time' => Carbon::parse($jadwal->waktu_mulai)->format('H:i') . ' - ' . Carbon::parse($jadwal->waktu_selesai)->format('H:i'),
                    'room' => $jadwal->ruangan->nama ?? '-',
                    'type' => 'kuliah',
                ];
            });

        // Kegiatan Mendatang
        $kegiatan = Kegiatan::where('status', 'disetujui')
            ->where('waktu_mulai', '>=', $today)
            ->with(['ruangan', 'user'])
            ->orderBy('waktu_mulai')
            ->limit(5)
            ->get()
            ->map(function ($kegiatan) {
                $startTime = Carbon::parse($kegiatan->waktu_mulai);
                $endTime = Carbon::parse($kegiatan->waktu_selesai);

                // Format time differently if it's for today vs a future date
                $timeFormat = $startTime->isToday() ? 'H:i' : 'd M, H:i';

                return [
                    'title' => $kegiatan->nama_kegiatan,
                    'time' => $startTime->translatedFormat($timeFormat) . ' - ' . $endTime->format('H:i'),
                    'room' => $kegiatan->ruangan->nama ?? '-',
                    'pic' => $kegiatan->user->name ?? $kegiatan->nama_pic,
                    'type' => 'kegiatan',
                ];
            });

        $response = [
            'jadwal_kuliah_hari_ini' => $jadwalKuliah,
            'kegiatan_mendatang' => $kegiatan,
        ];

        return response()->json($response);
    }
}
