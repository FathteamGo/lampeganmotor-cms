<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;

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

    /**
     * Override content() untuk menambahkan tombol action di atas widgets
     */
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Tombol AI Report & Insight
                View::make('filament.pages.dashboard-buttons')
                    ->columnSpanFull(),

                // Filter form
                ...(method_exists($this, 'getFiltersForm') ? [$this->getFiltersFormContentComponent()] : []),

                // Widgets
                $this->getWidgetsContentComponent(),
            ]);
    }

    /**
     * Livewire method: dispatch weekly report job
     */
    public function runWeeklyReport(): void
    {
        try {
            \App\Jobs\GenerateWeeklyReportJob::dispatch();

            Notification::make()
                ->title('Hana AI sedang bekerja! 🌸')
                ->body('Laporan mingguan sedang dipersiapkan di latar belakang untuk dikirimkan ke WhatsApp Anda, Bos! 😊')
                ->success()
                ->duration(8000)
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal memproses laporan')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->duration(8000)
                ->send();
        }
    }

    /**
     * Livewire method: dispatch 30-day insight job
     */
    public function run30DayInsight(): void
    {
        try {
            \App\Jobs\Generate30DayInsightJob::dispatch();

            Notification::make()
                ->title('Hana AI sedang merangkum! 🌸')
                ->body('Insight bisnis strategis 30 hari ke belakang sedang dipersiapkan di latar belakang untuk dikirimkan ke WhatsApp Anda, Bos! 😊')
                ->success()
                ->duration(8000)
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal memproses insight')
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
