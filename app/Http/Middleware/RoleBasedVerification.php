<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleBasedVerification
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user(); // Ambil user yang sedang login
        $kegiatan = $request->route('kegiatan'); // Ambil model kegiatan dari route

        // Tentukan role yang diizinkan berdasarkan status kegiatan
        $allowedRoles = [];
        switch ($kegiatan->status) {
            case 'belum_disetujui':
                $allowedRoles = ['Operator'];
                break;

            case 'verifikasi_akademik':
                $allowedRoles = ['Akademik'];
                break;

            case 'verifikasi_sarpras':
                $allowedRoles = ['Sarpras'];
                break;

            default:
                return redirect()->back()->with('error', 'Status tidak valid untuk memverifikasi!');
        }

        // Periksa apakah user memiliki salah satu role yang diizinkan
        if (!$user->roles->pluck('title')->map(fn($role) => strtolower(trim($role)))->intersect(
            collect($allowedRoles)->map(fn($role) => strtolower(trim($role)))
        )->count()) {
            return redirect()->back()->withErrors('Anda tidak memiliki izin untuk verifikasi.');
        }

        \Log::info('Request diteruskan ke controller');
        return $next($request); // Lanjutkan ke request berikutnya jika lolos
    }
}
