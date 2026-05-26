<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportService;
use App\Services\OpenRouterService;
use App\Services\WaService;
use Illuminate\Support\Number;
use App\Models\WhatsAppNumber;
use App\Models\WeeklyReport;

class SendWeeklyReport extends Command
{
    protected $signature = 'report:weekly';
    protected $description = 'Kirim laporan mingguan ke WhatsApp gateway';

    public function handle(
        ReportService $reportService,
        OpenRouterService $openRouter,
        WaService $wa
    ) {
        // Simpan laporan minggu ini (dengan insight AI)
        $report = $reportService->saveWeeklyReport($openRouter);

        // Cari laporan minggu lalu
        $lastWeek = WeeklyReport::where('end_date', '<', $report->start_date)
            ->latest('end_date')
            ->first();

        // Motor terlaris
        $topMotors = collect($report->top_motors)
            ->map(fn($m) => "• {$m['name']} → {$m['unit']} unit")
            ->implode("\n") ?: "Belum ada penjualan minggu ini";

        // Perbandingan
        if ($lastWeek) {
            $salesDiff = $report->sales_count - $lastWeek->sales_count;
            $salesPercent = $lastWeek->sales_count > 0
                ? round(($salesDiff / $lastWeek->sales_count) * 100, 1)
                : 0;

            $incomeDiff = $report->total_income - $lastWeek->total_income;
            $incomePercent = $lastWeek->total_income > 0
                ? round(($incomeDiff / $lastWeek->total_income) * 100, 1)
                : 0;

            $comparison =
                "📊 Perbandingan dengan minggu lalu:\n" .
                "• Penjualan: {$report->sales_count} unit (" .
                ($salesDiff >= 0 ? "naik" : "turun") . " {$salesPercent}%)\n" .
                "• Pemasukan: " . Number::currency($report->total_income, 'IDR', 'id', 0) .
                " (" . ($incomeDiff >= 0 ? "naik" : "turun") . " {$incomePercent}%)\n";
        } else {
            $comparison = "📊 Belum ada data minggu lalu untuk perbandingan.";
        }

        // 5️⃣ Susun pesan
        $message =
            "🤖 Halo, saya Royal Zero, asisten AI Anda.\n\n" .
            "📆 Laporan Mingguan Lampegan\n" .
            "{$report->start_date} - {$report->end_date}\n\n" .
            "1. Pengunjung: {$report->visitors}\n" .
            "2. Penjualan: {$report->sales_count} unit (" . Number::currency($report->sales_total, 'IDR', 'id', 0) . ")\n" .
            "3. Pemasukan: " . Number::currency($report->total_income, 'IDR', 'id', 0) . "\n" .
            "4. Pengeluaran: " . Number::currency($report->expense_total, 'IDR', 'id', 0) . "\n" .
            "5. Stok tersedia: {$report->stock}\n" .
            "6. Perpanjangan STNK: {$report->stnk_renewal}\n\n" .
            "🏆 Motor Terlaris:\n{$topMotors}\n\n" .
            "💡 Insight:\n{$report->insight}\n\n" .
            $comparison . "\n\n" .
            "⚠️ Disclaimer: Laporan ini dibuat otomatis oleh sistem AI. Periksa kembali sebelum digunakan untuk keputusan bisnis.";

        // Cari nomor WA gateway
        $number = WhatsAppNumber::where('is_active', true)
            ->where('is_report_gateway', true)
            ->value('number');

        if (!$number) {
            $this->error("❌ Nomor WhatsApp gateway belum diatur.");
            return Command::FAILURE;
        }

        // Kirim WA
        if ($wa->sendText($number, $message)) {
            $this->info("✅ Laporan terkirim ke nomor gateway ($number).");
            return Command::SUCCESS;
        }

        $this->error("❌ Gagal mengirim laporan ke WhatsApp.");
        return Command::FAILURE;
    }
}
