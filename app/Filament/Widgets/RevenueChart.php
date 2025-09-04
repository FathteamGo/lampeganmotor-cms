<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    // heading NON-STATIC, bisa pakai __()
    protected ?string $heading = null;

    // sort HARUS static
    protected static ?int $sort = 3;

    public function getHeading(): ?string
    {
        return __('widgets.revenue_info');
    }

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
                    'label' => __('widgets.revenue'),
                    'data' => [$thisMonthRevenue, $lastMonthRevenue],
                    'backgroundColor' => [
                        'rgb(54, 162, 235)',
                        'rgb(255, 99, 132)',
                    ],
                ],
            ],
            'labels' => [
                __('widgets.this_month'),
                __('widgets.last_month'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
