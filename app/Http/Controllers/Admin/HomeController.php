<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EventService;
use App\Models\Kegiatan;
use App\Models\Ruangan;
use App\Models\User;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController
{
    public function index()
    {
        $today = Carbon::today(); // Mendapatkan tanggal hari ini tanpa waktu
        $now = Carbon::now(); // Mendapatkan waktu saat ini

        $kegiatans = Kegiatan::whereDate('waktu_mulai', $today)
                    ->orWhereDate('waktu_selesai', $today)
                    ->where('deskripsi', !'Kuliah')
                    ->with(['ruangan', 'user']) // Relasi ruangan dan user jika dibutuhkan
                    ->orderBy('waktu_mulai', 'asc') // Mengurutkan berdasarkan waktu mulai dari yang paling awal
                    ->get()
                    ->map(function ($kegiatan) use ($now) {
                        // Tambahkan properti is_ongoing jika waktu saat ini berada di antara waktu_mulai dan waktu_selesai
                        $kegiatan->is_ongoing = $now->between($kegiatan->waktu_mulai, $kegiatan->waktu_selesai);
                        return $kegiatan;
                    });
        
        return view('home', compact('kegiatans'));
    }
}
