<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AssetSummaryTable extends BaseWidget
{
    protected static ?string $heading = 'Asset';
    protected int|string|array $columnSpan = 'full';

    // Diisi dari Page via @livewire(...)
    public float $totalSales = 0.0;
    public float $totalIncomes = 0.0;
    public float $totalExpenses = 0.0;

    /** Susun data non-eloquent untuk tabel */
    protected function makeRows(): array
    {
        $profit = $this->totalSales + $this->totalIncomes - $this->totalExpenses;

        return [
            ['type' => 'row',   'label' => 'SALE',    'amount' => $this->totalSales],
            ['type' => 'row',   'label' => 'INCOME',  'amount' => $this->totalIncomes],
            // EXPENSE tanpa minus:
            ['type' => 'row',   'label' => 'EXPENSE', 'amount' => $this->totalExpenses],
            ['type' => 'total', 'label' => 'Total',   'profit' => $profit],
        ];
    }

    public function table(Table $table): Table
    {
        $fmt = fn (float $n) => number_format($n, 0, ',', '.');

        return $table
            // Filament v4: records harus Closure
            ->records(fn () => $this->makeRows())
            ->paginated(false)
            ->columns([
                // Kolom kiri: label
                Tables\Columns\TextColumn::make('label_display')
                    ->label('')
                    ->getStateUsing(function ($record) {
                        if (!is_array($record)) return null;
                        return ($record['type'] ?? null) === 'total'
                            ? 'Total'
                            : strtoupper((string) ($record['label'] ?? ''));
                    })
                    ->weight('semibold')
                    ->extraAttributes(function ($record) {
                        // Biar kolom kanan nempel ujung kanan, bikin kolom kiri "melar"
                        $cls = 'w-full';
                        if (is_array($record) && (($record['type'] ?? null) !== 'total')) {
                            $cls .= ' uppercase tracking-wider';
                        }
                        return ['class' => $cls];
                    }),

                // Kolom kanan: nilai (selalu rata kanan)
                Tables\Columns\TextColumn::make('amount_display')
                    ->label('')
                    ->getStateUsing(function ($record) use ($fmt) {
                        if (!is_array($record)) return null;

                        if (($record['type'] ?? null) === 'total') {
                            $p = (float) ($record['profit'] ?? 0);
                            $sign = $p < 0 ? '-' : '';
                            return $sign . $fmt(abs($p)) . ' ' . ($p < 0 ? 'Loss' : 'Profit');
                        }

                        // Expense tanpa minus; lainnya normal
                        $amount = (float) ($record['amount'] ?? 0);
                        return $fmt($amount);
                    })
                    ->extraAttributes(function ($record) {
                        $classes = 'text-right tabular-nums';
                        if (is_array($record) && (($record['type'] ?? null) === 'total')) {
                            $p = (float) ($record['profit'] ?? 0);
                            $classes .= $p < 0 ? ' text-red-600' : ' text-green-600';
                        }
                        return ['class' => $classes];
                    }),
            ])
            // Strip abu untuk 3 baris pertama, dan abu-muda untuk baris total
            ->recordClasses(function ($record) {
                if (!is_array($record)) return null;

                return match ($record['type'] ?? null) {
                    'row'   => 'bg-gray-200 dark:bg-white/10',
                    'total' => 'bg-gray-100 dark:bg-white/5',
                    default => null,
                };
            });
    }
}
