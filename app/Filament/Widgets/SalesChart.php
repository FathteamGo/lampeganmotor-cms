<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Penjualan Statistic';

    // sort HARUS bersifat STATIC
    protected static ?int $sort = 2; // Urutan widget di dashboard

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
                    'label' => 'Penjualan',
                    'data' => $data,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
