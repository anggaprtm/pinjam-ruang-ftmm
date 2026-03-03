<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Schema; 
use App\Models\BotSetting;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('sync:google-calendar')->everyFifteenMinutes();
        $schedule->command('attendance:sync')->weekdays()->at('08:30')->timezone('Asia/Jakarta');
        $schedule->command('attendance:sync')->weekdays()->at('23:00')->timezone('Asia/Jakarta');

        // --- BACA SETTING DARI DATABASE ---
        if (Schema::hasTable('bot_settings')) {
            $bot = BotSetting::first();

            if ($bot) {
                // Parameter 'pagi' dikirim ke Command (Tendik & Dosen)
                if ($bot->pagi_aktif) {
                    $schedule->command('attendance:remind pagi')
                             ->weekdays()->at(substr($bot->pagi_jam, 0, 5))->timezone('Asia/Jakarta');
                }
                
                // Parameter 'masuk' dikirim ke Command (Tendik)
                if ($bot->masuk_aktif) {
                    $schedule->command('attendance:remind masuk')
                             ->weekdays()->at(substr($bot->masuk_jam, 0, 5))->timezone('Asia/Jakarta');
                }
                
                // Parameter 'pulang' dikirim ke Command (Tendik)
                if ($bot->pulang_aktif) {
                    $schedule->command('attendance:remind pulang')
                             ->weekdays()->at(substr($bot->pulang_jam, 0, 5))->timezone('Asia/Jakarta');
                }
                
                // Parameter 'evaluasi' dikirim ke Command (Tendik)
                if ($bot->evaluasi_aktif) {
                    $schedule->command('attendance:remind evaluasi')
                             ->weekdays()->at(substr($bot->evaluasi_jam, 0, 5))->timezone('Asia/Jakarta');
                }

                // ==========================================================
                // TAMBAHAN: Parameter 'siang_dosen' dikirim ke Command (Khusus Dosen)
                // ==========================================================
                if ($bot->siang_dosen_aktif) {
                    $schedule->command('attendance:remind siang_dosen')
                             ->weekdays()->at(substr($bot->siang_dosen_jam, 0, 5))->timezone('Asia/Jakarta');
                }
            }
        }

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}