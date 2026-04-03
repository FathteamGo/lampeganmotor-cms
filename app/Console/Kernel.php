<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Sinkronisasi status vehicle setiap 5 menit
        $schedule->command('vehicles:sync-status')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onFailure(function () {
                Log::error('Vehicle status sync failed!');
            })
            ->onSuccess(function () {
                Log::info('Vehicle status sync completed successfully');
            });

        // Optional: Daily full sync pada jam 2 pagi untuk lebih thorough check
        $schedule->command('vehicles:sync-status --force')
            ->dailyAt('02:00')
            ->withoutOverlapping();
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
