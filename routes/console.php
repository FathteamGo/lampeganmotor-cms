<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\WhatsAppNumber;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| Di sini kamu bisa mendaftarkan command artisan custom
| dan juga scheduling task yang otomatis jalan.
|
*/

// Command contoh inspire
Artisan::command('inspire', function () {
    try {
        $this->comment(Inspiring::quote());
        $this->info('Berhasil menampilkan quote.');
    } catch (\Exception $e) {
        $this->error('Gagal menampilkan quote: ' . $e->getMessage());
    }
})->purpose('Display an inspiring quote');

// Scheduler laporan mingguan + insight 30 hari (setiap Minggu jam 01:00)
Schedule::call(function () {
    // Dispatch weekly report
    \App\Jobs\GenerateWeeklyReportJob::dispatch();

    // Dispatch 30-day insight (delay 30 detik agar tidak bentrok rate limit AI/WA)
    \App\Jobs\Generate30DayInsightJob::dispatch()->delay(now()->addSeconds(30));

    \Log::info('Scheduler: Weekly report & 30-day insight dispatched (Minggu 01:00).');
})
    ->weeklyOn(0, '01:00') // 0 = Minggu
    ->name('weekly-report-and-insight')
    ->withoutOverlapping()
    ->before(function () {
        // Cek apakah nomor WA gateway ada
        $number = WhatsAppNumber::where('is_active', true)
            ->where('is_report_gateway', true)
            ->value('number');

        if (! $number) {
            \Log::error('❌ Report scheduler dibatalkan: Nomor WhatsApp gateway belum diatur.');
            return false;
        }
    })
    ->onSuccess(function () {
        \Log::info('✅ Weekly report & 30-day insight scheduler berhasil dijalankan.');
    })
    ->onFailure(function () {
        \Log::error('❌ Weekly report & 30-day insight scheduler gagal dieksekusi.');
    });

// Scheduler laporan mingguan (legacy command - backup jam 03:00)
Schedule::command('report:weekly')
        ->weeklyOn(0, '3:00') 
        // ->everySecond()
        ->before(function () {
        // cek apakah nomor WA gateway ada
        $number = WhatsAppNumber::where('is_active', true)
            ->where('is_report_gateway', true)
            ->value('number');

        if (! $number) {
            $msg = "❌ Report scheduler dibatalkan: Nomor WhatsApp gateway belum diatur.";
            \Log::error($msg);
            echo $msg . "\n";
            return false; // hentikan eksekusi command
        }
    })
    ->onSuccess(function () {
        $msg = "✅ Report scheduler berhasil dijalankan.";
        \Log::info($msg);
        echo $msg . "\n";
    })
    ->onFailure(function () {
        $msg = "❌ Report scheduler gagal dieksekusi.";
        \Log::error($msg);
        echo $msg . "\n";
    });
