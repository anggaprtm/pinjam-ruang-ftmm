<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\Kegiatan;
use App\Models\PermintaanKegiatan;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Update bagian View::composer ini
        View::composer('partials.menu', function ($view) {
            
            // 1. Hitung Pending Kegiatan (Logic lama kamu)
            $kegiatanCount = Cache::remember('pending_kegiatan_count', 60, function () {
                return Kegiatan::whereNotIn('status', ['disetujui', 'ditolak'])->count();
            });

            // 2. Hitung Pending Permintaan (Logic baru)
            $permintaanCount = Cache::remember('pending_permintaan_count', 60, function () {
                return PermintaanKegiatan::where('status_permintaan', 'pending')->count();
            });

            // 3. Jumlahkan keduanya untuk Badge Dashboard
            $totalDashboardPending = $kegiatanCount + $permintaanCount;

            // Kirim semua variabel ke view
            $view->with('pendingKegiatanCount', $kegiatanCount)
                ->with('totalDashboardPending', $totalDashboardPending)
                ->with('pendingPermintaanCount', $permintaanCount);
        });
    }
}
