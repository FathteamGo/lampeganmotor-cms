<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Command contoh inspire
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// // Schedule command report:weekly per menit
// Schedule::command('report:weekly')->everyMinute();
Schedule::command('report:weekly')->weeklyOn(0, '3:00');

