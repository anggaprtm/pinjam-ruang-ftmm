<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleBasedVerification
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $kegiatan = $request->route('kegiatan');

        $allowedRoles = [];
        switch ($kegiatan->status) {
            case 'belum_disetujui':
                $allowedRoles = ['Operator', 'Admin']; 
                break;

            case 'verifikasi_kemahasiswaan':
                // Masukkan nama Role yang sesuai di DB kamu untuk Kemahasiswaan
                $allowedRoles = ['Kemahasiswaan', 'Staf Kemahasiswaan']; 
                break;

            case 'verifikasi_kasubag_akademik':
                $allowedRoles = ['Kasubag Akademik', 'Akademik'];
                break;

            case 'verifikasi_kasubag_sarpras':
                // Jika tahap ini yang harus klik adalah Operator (untuk finalisasi),
                // maka masukkan 'Operator'. Jika Sarpras yang klik, masukkan 'Sarpras'.
                // Mengikuti request "Disetujui (operator yg aksi)":
                $allowedRoles = ['Operator', 'Admin', 'Sarpras']; 
                break;

            default:
                // Jika status revisi, biasanya yang boleh akses adalah pembuat (Operator)
                if (\Str::startsWith($kegiatan->status, 'revisi_')) {
                    $allowedRoles = ['Operator', 'User'];
                }
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
