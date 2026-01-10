<?php

namespace App\Filament\Pages;

use App\Models\WeeklyReport;
use App\Models\WhatsAppNumber;
use App\Services\WaService;
use Carbon\Carbon;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    /**
     * Filter utama Dashboard
     * Menampilkan filter per bulan & tahun (default: bulan & tahun sekarang)
     */
    public function filtersForm(Schema $schema): Schema
    {
        $currentMonth = now()->format('m');
        $currentYear  = now()->year;

        return $schema->components([
            Section::make('Filter Periode')->schema([
                Select::make('month')
                    ->label('Bulan')
                    ->options([
                        '01' => 'Januari',
                        '02' => 'Februari',
                        '03' => 'Maret',
                        '04' => 'April',
                        '05' => 'Mei',
                        '06' => 'Juni',
                        '07' => 'Juli',
                        '08' => 'Agustus',
                        '09' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ])
                    ->default($currentMonth),

                Select::make('year')
                    ->label('Tahun')
                    ->options(function () use ($currentYear) {
                        $years = [];
                        for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                            $years[(string) $i] = $i;
                        }
                        return $years;
                    })
                    ->default((string) $currentYear),
            ])->columns(2)->columnSpanFull(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('runSample')
                ->label('Run Report AI Agent')
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Generate & Send Report?')
                ->modalDescription('Sistem akan membuat laporan mingguan dengan AI insight dan mengirimnya via WhatsApp.')
                ->modalSubmitActionLabel('Ya, Kirim Sekarang')
                ->action('runSample'),

            Action::make('weeklyReportNotif')
                ->label('Ada Insight Terbaru')
                ->modalHeading('Insight Baru')
                ->modalSubheading('Ada laporan mingguan baru yang belum dibaca.')
                ->modalContent(view('filament.components.weekly-report-notif'))
                ->modalButton('Tandai Sudah Dibaca')
                ->modalWidth('lg')
                ->hidden(fn () => !WeeklyReport::where('read', 0)->exists())
                ->action(function () {
                    WeeklyReport::where('read', 0)->update(['read' => 1]);
                    Notification::make()
                        ->title('Laporan ditandai sudah dibaca')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function runSample(): void
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

            // --- Siapkan message ---
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

            $topMotors = collect($report->top_motors)
                ->map(fn($m) => "• {$m['name']} → {$m['unit']} unit")
                ->implode("\n") ?: "Belum ada penjualan minggu ini";

            $startDate = Carbon::parse($report->start_date)->format('d M Y');
            $endDate   = Carbon::parse($report->end_date)->format('d M Y');

            $message =
                "🤖 Halo, saya Royal Zero, asisten AI Anda.\n\n" .
                "📆 Report Lampegan Motor Periode \n" .
                "{$startDate} - {$endDate}\n\n" .
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

            try {
                app(WaService::class)->sendText($number, $message);

                Notification::make()
                    ->title('Sample report berhasil dibuat & dikirim via WhatsApp')
                    ->success()
                    ->duration(5000)
                    ->send();
            } catch (\Throwable $waError) {
                // Tangkap error WA, tampilkan sebagai danger notification
                Notification::make()
                    ->title('Gagal mengirim laporan via WhatsApp')
                    ->body('Error WA: ' . $waError->getMessage())
                    ->danger()
                    ->duration(8000)
                    ->send();

                // Stop process, jangan tampilkan success
                return;
            }

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal membuat laporan')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->duration(8000)
                ->send();
        }
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
            \App\Filament\Widgets\WeeklyReportModal::class,
        ];
    }
}
