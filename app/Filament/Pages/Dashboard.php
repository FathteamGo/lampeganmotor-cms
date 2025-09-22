<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Notifications\Notification; // <- import notification

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label(fn () => __('dashboard.start_date'))
                            ->default(Carbon::now())
                            ->maxDate(fn (Get $get) => $get('endDate') ?: Carbon::now()),

                        DatePicker::make('endDate')
                            ->label(fn () => __('dashboard.end_date'))
                            ->default(Carbon::now())
                            ->minDate(fn (Get $get) => $get('startDate') ?: Carbon::now())
                            ->maxDate(Carbon::now()),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
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
            Action::make('sample')
                ->label('Run Report AI Agent')
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->action(fn () => $this->runSample()),
        ];
    }

protected function runSample(): void
{
    $report = app(\App\Services\ReportService::class)
        ->saveWeeklyReport(app(\App\Services\GeminiService::class));
        

    $totalSalesFormatted = number_format($report->sales_total, 0, ',', '.');
    $incomeFormatted     = number_format($report->income_total, 0, ',', '.');
    $expenseFormatted    = number_format($report->expense_total, 0, ',', '.');

    \Filament\Notifications\Notification::make()
        ->title('Sample report berhasil dibuat!')
        ->body(
            "Periode: {$report->start_date} - {$report->end_date}\n\n" 
            // "Penjualan: {$report->sales_count} unit, total Rp {$totalSalesFormatted}\n" .
            // "Pemasukan: Rp {$incomeFormatted}\n" .
            // "Pengeluaran: Rp {$expenseFormatted}\n\n" .
            // "Insight:\n{$report->insight}"
        )
        ->success()
        ->duration(5000)
        ->send();

    app(\App\Services\WaService::class)->sendText(
        // '6281394510605',
        '6283841806357',
        "Sample report Lampegan Motor berhasil dibuat!\n" .
        "Periode: {$report->start_date} - {$report->end_date}\n" .
        "Pengunjung: {$report->visitors}\n" .
        "Penjualan: {$report->sales_count} unit, total Rp {$totalSalesFormatted}\n" .
        "Pemasukan: Rp {$incomeFormatted}\n" .
        "Pengeluaran: Rp {$expenseFormatted}\n" .
        "Stok: {$report->stock}\n" .
        "Perpanjangan STNK: {$report->stnk_renewal}\n" .
        "Motor Terlaris: " . collect($report->top_motors)->map(fn($m) => "{$m['name']} → {$m['unit']} unit")->implode(', ') . "\n\n" .
        "Insight:\n{$report->insight}"
    );
}



}


