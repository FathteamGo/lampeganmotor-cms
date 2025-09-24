<?php

namespace App\Filament\Pages;

use App\Models\WeeklyReport;
use App\Models\WhatsAppNumber;
use App\Services\WaService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
            DatePicker::make('startDate')
                ->label('Tanggal Mulai')
                ->default(Carbon::now())
                ->maxDate(fn(Get $get) => $get('endDate') ?: Carbon::now()),
            DatePicker::make('endDate')
                ->label('Tanggal Selesai')
                ->default(Carbon::now())
                ->minDate(fn(Get $get) => $get('startDate') ?: Carbon::now())
                ->maxDate(Carbon::now()),
            ])->columns(2)->columnSpanFull(),
        ]);
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\DashboardStats::class,
            \App\Filament\Widgets\VisitorStats::class,
            \App\Filament\Widgets\VisitorChart::class,
            \App\Filament\Widgets\SalesChart::class,
            \App\Filament\Widgets\PopularVehicleWidget::class,
            \App\Filament\Widgets\PopularBlogWidget::class,
            \App\Filament\Widgets\RevenueChart::class,
        ];
    }

   protected function getHeaderActions(): array
{
    return [
        // Run Report via AI
        Action::make('runSample')
            ->label('Run Report AI Agent')
            ->icon('heroicon-o-bolt')
            ->color('success')
            ->action(fn() => $this->runSample()),

        // Modal Notifikasi WeeklyReport terbaru
        Action::make('weeklyReportNotif')
            ->label('Insight Baru')
            ->modalHeading('Insight Baru')
            ->modalSubheading('Ada laporan mingguan baru yang belum dibaca.')
            ->modalContent(view('filament.components.weekly-report-notif')) // Blade modal
            ->modalButton('Tandai Sudah Dibaca')
            ->modalWidth('lg')
            ->hidden(fn () => ! \App\Models\WeeklyReport::where('read', 0)->exists())
            ->action(function () {
                \App\Models\WeeklyReport::where('read', 0)->update(['read' => 1]);
                Notification::make()
                    ->title('Laporan ditandai sudah dibaca')
                    ->success()
                    ->send();
            }),
    ];
}

    protected function runSample(): void
    {
        try {
            $report = app(\App\Services\ReportService::class)
                ->saveWeeklyReport(app(\App\Services\GeminiService::class));

            $number = WhatsAppNumber::where('is_active', true)
                ->where('is_report_gateway', true)
                ->value('number');

            if (!$number) {
                throw new \Exception('Nomor WhatsApp gateway belum diatur.');
            }

            // Ambil laporan minggu lalu
            $lastWeek = WeeklyReport::where('end_date', '<', $report->start_date)
                ->latest('end_date')
                ->first();

            $comparison = "📊 Belum ada data minggu lalu untuk perbandingan.";
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

            // Motor terlaris
            $topMotors = collect($report->top_motors)
                ->map(fn($m) => "• {$m['name']} → {$m['unit']} unit")
                ->implode("\n") ?: "Belum ada penjualan minggu ini";

            $message =
                "🤖 Halo, saya Royal Zero, asisten AI Anda.\n\n" .
                "📆 Report Lampegan Motor Periode \n" .
                "{$report->start_date} - {$report->end_date}\n\n" .
                "1. Pengunjung: {$report->visitors}\n" .
                "2. Penjualan: {$report->sales_count} unit (Rp " . number_format($report->sales_total, 0, ',', '.') . ")\n" .
                "3. Pemasukan: Rp " . number_format($report->total_income, 0, ',', '.') . "\n" .
                "4. Pengeluaran: Rp " . number_format($report->expense_total, 0, ',', '.') . "\n" .
                "5. Stok tersedia: {$report->stock}\n" .
                "6. Perpanjangan STNK: {$report->stnk_renewal}\n\n" .
                "🏆 Motor Terlaris:\n{$topMotors}\n\n" .
                "💡 Insight:\n{$report->insight}\n\n" .
                $comparison . "\n\n" .
                "⚠️ Disclaimer: Laporan ini dibuat otomatis oleh sistem AI. Periksa kembali sebelum digunakan untuk keputusan bisnis.";

            app(WaService::class)->sendText($number, $message);

            Notification::make()
                ->title('Sample report berhasil dibuat & dikirim via WhatsApp')
                ->success()
                ->duration(5000)
                ->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal mengirim laporan via WhatsApp')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->duration(8000)
                ->send();
        }
    }
}
