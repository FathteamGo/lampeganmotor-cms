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

class Generate30DayInsightJob implements ShouldQueue
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
            // Generate 30-day report data
            $data = $reportService->generate30DayReportData();
            
            // Generate AI insight
            $insight = $reportService->generate30DayInsight($data, $openRouter);

            // Build message
            $message = $notificationService->build30DayInsightMessage($data, $insight);

            // Get the WhatsApp gateway number
            $number = env('WHATSAPP_NUMBER'); // using env as in the original route to be safe, though model is better. Let's use both as fallback.
            if (!$number) {
                 $number = WhatsAppNumber::where('is_active', true)
                    ->where('is_report_gateway', true)
                    ->value('number');
            }

            if (!$number) {
                Log::warning('Generate30DayInsightJob: Nomor WhatsApp gateway belum diatur.');
                return;
            }

            // Send message
            $waService->sendText($number, $message);

            Log::info('Generate30DayInsightJob: 30-day Insight berhasil dikirim ke ' . $number);
        } catch (\Throwable $e) {
            Log::error('Generate30DayInsightJob failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
