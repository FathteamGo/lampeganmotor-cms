<?php

namespace App\Filament\Widgets;

use App\Models\WeeklyReport;
use Filament\Widgets\Widget;

class WeeklyReportModal extends Widget
{
    protected string $view = 'filament.widgets.weekly-report-modal';

    public $open = false;
    public $title = '';
    public $date = '';
    public $content = '';

    public function mount(): void
    {
        $report = WeeklyReport::latest()->first();

        if (! $report) {
            $this->open = false;
            return;
        }

        $this->title = '📊 Weekly Insight Terbaru';
        $this->date = $this->formatDateRange($report);
        $this->content = $this->formatReport($report);
        $this->open = true;
    }

    protected function formatDateRange(WeeklyReport $report): string
    {
        $start = $report->start_date ? $report->start_date->format('d M Y') : null;
        $end = $report->end_date ? $report->end_date->format('d M Y') : null;
        if ($start && $end) return "{$start} - {$end}";
        return $report->created_at ? $report->created_at->format('d M Y') : '';
    }

    protected function formatReport(WeeklyReport $report): string
    {
        $lastWeek = WeeklyReport::where('end_date', '<', $report->start_date)
            ->latest('end_date')
            ->first();

        $comparison = "Belum ada data minggu lalu.";
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
                "• Pemasukan: Rp " . number_format($report->total_income, 0, ',', '.') .
                " (" . ($incomeDiff >= 0 ? "naik" : "turun") . " {$incomePercent}%)";
        }

        $topMotors = collect($report->top_motors ?? [])
            ->map(fn($m) => "• {$m['name']} → {$m['unit']} unit")
            ->implode("\n") ?: "Belum ada penjualan minggu ini";

        return
            "1. Pengunjung: {$report->visitors}\n" .
            "2. Penjualan: {$report->sales_count} unit\n" .
            "3. Pemasukan: Rp " . number_format($report->total_income, 0, ',', '.') . "\n" .
            "4. Pengeluaran: Rp " . number_format($report->expense_total, 0, ',', '.') . "\n" .
            "5. Stok tersedia: {$report->stock}\n" .
            "6. Perpanjangan STNK: {$report->stnk_renewal}\n\n" .
            "🏆 Motor Terlaris:\n{$topMotors}\n\n" .
            "💡 Insight:\n{$report->insight}\n\n" .
            $comparison;
    }
}