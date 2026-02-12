<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('sync:google-calendar')->everyFifteenMinutes();

        // 1. Reminder Pagi (06:30) - Senin s/d Jumat
        $schedule->command('attendance:remind')
                 ->weekdays()
                 ->at('06:30')
                 ->timezone('Asia/Jakarta');

        // 2. Warning Belum Masuk (07:50) - Senin s/d Jumat
        $schedule->command('attendance:remind')
                 ->weekdays()
                 ->at('07:50')
                 ->timezone('Asia/Jakarta');

        // 3. Reminder Pulang (17:00) - Senin s/d Jumat
        $schedule->command('attendance:remind')
                 ->weekdays()
                 ->at('17:00')
                 ->timezone('Asia/Jakarta');

        // 4. Evaluasi Malam (19:00) - Senin s/d Jumat 
        $schedule->command('attendance:remind')
                 ->weekdays()
                 ->at('19:00')
                 ->timezone('Asia/Jakarta');

        $schedule->command('attendance:sync')
                 ->weekdays()
                 ->at('08:30')
                 ->timezone('Asia/Jakarta');

        $schedule->command('attendance:sync')
                 ->weekdays()
                 ->at('23:00')
                 ->timezone('Asia/Jakarta');

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
