<?php

namespace App\Jobs;

use App\Models\WhatsAppNumber;
use App\Services\OpenRouterService;
use App\Services\ReportNotificationService;
use App\Services\ReportService;
use App\Services\WaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateWeeklyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(
        ReportService $reportService,
        OpenRouterService $openRouter,
        ReportNotificationService $notificationService,
        WaService $waService,
    ): void {
        try {
            // 1. Generate and save the weekly report
            $report = $reportService->saveWeeklyReport($openRouter);

            // 2. Get the WhatsApp gateway number
            $number = env('WHATSAPP_NUMBER');
            if (!$number) {
                $number = WhatsAppNumber::where('is_active', true)
                    ->where('is_report_gateway', true)
                    ->value('number');
            }

            if (!$number) {
                Log::warning('GenerateWeeklyReportJob: Nomor WhatsApp gateway belum diatur.');
                return;
            }

            // 3. Build and send the message
            $message = $notificationService->buildReportMessage($report);
            $waService->sendText($number, $message);

            Log::info('GenerateWeeklyReportJob: Laporan mingguan berhasil dikirim ke ' . $number);
        } catch (\Throwable $e) {
            Log::error('GenerateWeeklyReportJob failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
