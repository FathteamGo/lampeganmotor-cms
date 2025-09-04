<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AssetSummaryTable extends BaseWidget
{
    protected static ?string $heading = null; // Heading null, pakai getHeading()
    protected int|string|array $columnSpan = 'full';

    // Data diisi dari Page via @livewire(...)
    public float $totalSales = 0.0;
    public float $totalIncomes = 0.0;
    public float $totalExpenses = 0.0;

    // Heading multi-bahasa
    protected function getHeading(): ?string
    {
        return __('tables.asset_summary');
    }

    /** Susun data non-eloquent untuk tabel */
    protected function makeRows(): array
    {
        $profit = $this->totalSales + $this->totalIncomes - $this->totalExpenses;

        return [
            ['type' => 'row',   'label' => __('tables.sale'),    'amount' => $this->totalSales],
            ['type' => 'row',   'label' => __('tables.income'),  'amount' => $this->totalIncomes],
            ['type' => 'row',   'label' => __('tables.expense'), 'amount' => $this->totalExpenses],
            ['type' => 'total', 'label' => __('tables.total'),   'profit' => $profit],
        ];
    }

    public function table(Table $table): Table
    {
        $fmt = fn (float $n) => number_format($n, 0, ',', '.');

        return $table
            ->records(fn () => $this->makeRows())
            ->paginated(false)
            ->columns([
                // Kolom kiri: label
                Tables\Columns\TextColumn::make('label_display')
                    ->label('')
                    ->getStateUsing(fn ($record) => 
                        is_array($record) 
                            ? (($record['type'] ?? null) === 'total' 
                                ? __('tables.total') 
                                : strtoupper((string) ($record['label'] ?? ''))) 
                            : null
                    )
                    ->weight('semibold')
                    ->extraAttributes(fn ($record) => [
                        'class' => 'w-full' . (is_array($record) && ($record['type'] ?? null) !== 'total' ? ' uppercase tracking-wider' : '')
                    ]),

                // Kolom kanan: nilai
                Tables\Columns\TextColumn::make('amount_display')
                    ->label('')
                    ->getStateUsing(function ($record) use ($fmt) {
                        if (!is_array($record)) return null;

                        if (($record['type'] ?? null) === 'total') {
                            $p = (float) ($record['profit'] ?? 0);
                            $sign = $p < 0 ? '-' : '';
                            return $sign . $fmt(abs($p)) . ' ' . ($p < 0 ? __('tables.loss') : __('tables.profit'));
                        }

                        return $fmt((float) ($record['amount'] ?? 0));
                    })
                    ->extraAttributes(fn ($record) => [
                        'class' => 'text-right tabular-nums' . (is_array($record) && ($record['type'] ?? null) === 'total' 
                            ? ((float) ($record['profit'] ?? 0) < 0 ? ' text-red-600' : ' text-green-600') 
                            : '')
                    ]),
            ])
            ->recordClasses(fn ($record) => match ($record['type'] ?? null) {
                'row'   => 'bg-gray-200 dark:bg-white/10',
                'total' => 'bg-gray-100 dark:bg-white/5',
                default => null,
            });
    }
}
