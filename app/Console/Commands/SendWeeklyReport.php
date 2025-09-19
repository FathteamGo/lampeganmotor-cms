<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportService;
use App\Services\GeminiService;
use App\Services\WaService;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class SendWeeklyReport extends Command
{
    protected $signature = 'report:weekly';
    protected $description = 'Kirim laporan mingguan ke Owner via WhatsApp';

    public function handle(
        ReportService $reportService,
        GeminiService $gemini,
        WaService $wa
    ) {
        // 1️⃣ Simpan laporan minggu ini (termasuk insight dari AI)
        $report = $reportService->saveWeeklyReport($gemini);

        // 2️⃣ Motor terlaris 5 besar (dari DB)
        $topMotors = collect($report->top_motors)->map(fn($m) => "• {$m['name']} → {$m['unit']} unit")->implode("\n");
        if (trim($topMotors) === '') {
            $topMotors = " Belum ada penjualan minggu ini";
        }

        // Insight sudah tersimpan di DB
        $insight = $report->insight ?? 'Belum ada insight';

        // Format pesan WA bersih
        $message =
            "📆 Laporan Mingguan Lampegan\n" .
            "{$report->start_date} - {$report->end_date}\n\n" .
            "1. Pengunjung: {$report->visitors}\n" .
            "2. Penjualan: {$report->sales_count} unit (" . Number::currency($report->sales_total, 'IDR', 'id', 0) . ")\n" .
            "3. Pemasukan: " . Number::currency($report->total_income, 'IDR', 'id', 0) . "\n" .
            "4. Pengeluaran: " . Number::currency($report->expense_total, 'IDR', 'id', 0) . "\n" .
            "5. Stok tersedia: {$report->stock}\n" .
            "6. Perpanjangan STNK: {$report->stnk_renewal}\n\n" .
            "🏆 Motor Terlaris:\n{$topMotors}\n\n" .
            "💡 Insight:\n{$insight}";

        // Kirim WA ke owner
        $owner = config('services.wa_gateway.owner');

        if ($wa->sendText($owner, $message)) {
            $this->info("✅ Laporan terkirim ke Owner ($owner).");
        } else {
            $this->error("❌ Gagal mengirim laporan ke Owner.");
        }
    }
}
