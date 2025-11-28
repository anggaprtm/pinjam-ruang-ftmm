<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\Kegiatan;

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
        // Provide pending kegiatan count to the sidebar view, cached for 60 seconds
        View::composer('partials.menu', function ($view) {
            $count = Cache::remember('pending_kegiatan_count', 60, function () {
                return Kegiatan::whereNotIn('status', ['disetujui', 'ditolak'])->count();
            });

            $view->with('pendingKegiatanCount', $count);
        });
    }
}
