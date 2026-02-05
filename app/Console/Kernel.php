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
        // 1. PENGINGAT PAGI (Baru)
        // Jalan jam 08:30 (Lewat batas masuk 08:00)
        // Fungsinya: Ngingetin yang statusnya masih "-" (Belum scan)
        $schedule->command('attendance:remind')
                 ->weekdays()
                 ->at('07:50')
                 ->timezone('Asia/Jakarta');

        // 2. REKAP MALAM (Tetap)
        // Jalan jam 17:00
        // Fungsinya: Rekap telat pagi & reminder pulang
        $schedule->command('attendance:remind')
                 ->weekdays()
                 ->at('17:00')
                 ->timezone('Asia/Jakarta');

        // 3. REKAP MALAM 
        // Jalan jam 20:30
        // Fungsinya: Rekap telat pagi & reminder pulang  
        $schedule->command('attendance:remind')
                 ->weekdays()
                 ->at('19:30')
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
