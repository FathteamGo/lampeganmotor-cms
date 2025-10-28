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
