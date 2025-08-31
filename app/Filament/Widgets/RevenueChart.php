<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    // heading bersifat NON-STATIC
    protected ?string $heading = 'Revenue Info';

    // sort HARUS bersifat STATIC
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $thisMonthRevenue = Sale::whereYear('sale_date', now()->year)
            ->whereMonth('sale_date', now()->month)
            ->sum('sale_price');

        $lastMonthRevenue = Sale::whereYear('sale_date', now()->subMonth()->year)
            ->whereMonth('sale_date', now()->subMonth()->month)
            ->sum('sale_price');

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => [$thisMonthRevenue, $lastMonthRevenue],
                    'backgroundColor' => [
                        'rgb(54, 162, 235)',
                        'rgb(255, 99, 132)',
                    ],
                ],
            ],
            'labels' => ['Bulan Ini', 'Bulan Lalu'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
