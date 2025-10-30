<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected ?string $heading = null;

    protected static ?int $sort = 2;

    public function getHeading(): ?string
    {
        return 'Statistik Penjualan';
    }

    protected function getData(): array
    {
        $salesData = Sale::selectRaw('MONTH(sale_date) as month, SUM(sale_price) as total')
            ->whereYear('sale_date', now()->year)
            ->whereNotIn('status', ['cancel', 'batal'])
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
                    'backgroundColor' => 'rgba(75, 192, 192, 0.3)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
