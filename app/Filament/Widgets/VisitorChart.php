<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Visitor;

class VisitorChart extends ChartWidget
{
    // <-- ubah jadi non-static (hilangkan `static`)
    protected ?string $heading = 'Statistik Pengunjung Bulanan';

    protected function getData(): array
    {
        $currentYear = now()->year;

        $data = Visitor::selectRaw('MONTH(visited_at) as bulan, COUNT(*) as total')
            ->whereYear('visited_at', $currentYear)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        $labels = [];
        $totals = [];

        foreach (range(1, 12) as $month) {
            $labels[] = date('F', mktime(0, 0, 0, $month, 1));
            $totals[] = isset($data[$month]) ? (int) $data[$month]->total : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pengunjung',
                    'data' => $totals,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
