<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

// 1. Ini perintah 'inspire' (biarkan dia sendiri)
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// 2. TARUH JADWAL DI LUAR KURUNG KURAWAL 'inspire' (Di sini tempat yang benar)
// -----------------------------------------------------------------------------

// Perintah Backup: Jalan setiap jam 01:00 Pagi
Schedule::command('backup:run')->dailyAt('01:00');

// Perintah Bersih-bersih backup lama: Jalan setiap jam 01:30 Pagi
Schedule::command('backup:clean')->dailyAt('01:30');