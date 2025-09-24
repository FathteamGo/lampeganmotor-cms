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

// Scheduler laporan mingguan
Schedule::command('report:weekly')
        ->weeklyOn(0, '3:00') 
        // ->everyMinute()
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
