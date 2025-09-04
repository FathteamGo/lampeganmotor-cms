<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected ?string $heading = null;

    // sort HARUS static
    protected static ?int $sort = 2;

    public function getHeading(): ?string
    {
        return __('widgets.sales_statistics');
    }

    protected function getData(): array
    {
        $salesData = Sale::selectRaw('MONTH(sale_date) as month, SUM(sale_price) as total')
            ->whereYear('sale_date', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->all();

        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[] = $salesData[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => __('widgets.sales'),
                    'data' => $data,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => [
                __('months.jan'),
                __('months.feb'),
                __('months.mar'),
                __('months.apr'),
                __('months.may'),
                __('months.jun'),
                __('months.jul'),
                __('months.aug'),
                __('months.sep'),
                __('months.oct'),
                __('months.nov'),
                __('months.dec'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
