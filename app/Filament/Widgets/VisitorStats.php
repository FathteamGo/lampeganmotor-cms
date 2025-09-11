<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VisitorStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hari Ini',
            'this_week' => 'Minggu Ini',
            'this_month' => 'Bulan Ini',
        ];
    }

    protected function getStats(): array
    {
        $filter = $this->filter ?? 'today';

        // Default hari ini
        $startDate = Carbon::today();
        $endDate = Carbon::today()->endOfDay();

        if ($filter === 'this_week') {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        } elseif ($filter === 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }

        $periodeIni = DB::table('visitors')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->count();

        $bulanIni = DB::table('visitors')
            ->whereMonth('visited_at', Carbon::now()->month)
            ->whereYear('visited_at', Carbon::now()->year)
            ->count();

        $tahunIni = DB::table('visitors')
            ->whereYear('visited_at', Carbon::now()->year)
            ->count();

        return [
            Stat::make('Pengunjung Periode Ini', $periodeIni)
                ->description("{$startDate->format('d M')} - {$endDate->format('d M Y')}"),

            Stat::make('Pengunjung Bulan Ini', $bulanIni)
                ->description(Carbon::now()->translatedFormat('F Y')),

            Stat::make('Pengunjung Tahun Ini', $tahunIni)
                ->description(Carbon::now()->year),
        ];
    }
}
