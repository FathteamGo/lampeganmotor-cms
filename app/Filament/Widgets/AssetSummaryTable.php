<?php

namespace App\Filament\Widgets;

use App\Models\StnkRenewal;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AssetSummaryTable extends BaseWidget
{
    protected static ?string $heading = null;
    protected int|string|array $columnSpan = 'full';

    public ?string $dateStart = null;
    public ?string $dateEnd   = null;

    // Data umum
    public float $totalSales    = 0.0;
    public float $totalIncomes  = 0.0;
    public float $totalExpenses = 0.0;

    // Data STNK (akan dihitung otomatis)
    public float $totalStnkIncome  = 0.0;
    public float $totalStnkExpense = 0.0;
    public float $totalStnkMargin  = 0.0;

    protected function getHeading(): ?string
    {
        return __('tables.asset_summary');
    }

    protected function makeRows(): array
    {
        // Hitung STNK langsung dari database
        $query = StnkRenewal::query()
            ->when($this->dateStart, fn($q) => $q->whereDate('tgl', '>=', $this->dateStart))
            ->when($this->dateEnd, fn($q) => $q->whereDate('tgl', '<=', $this->dateEnd));

        $this->totalStnkIncome = (float) $query->sum('total_pajak_jasa');
        $this->totalStnkMargin = (float) $query->sum('margin_total');
        $this->totalStnkExpense = $this->totalStnkIncome - $this->totalStnkMargin;

        // Hitung profit total
        $profit = $this->totalSales 
                 + $this->totalIncomes 
                 + $this->totalStnkIncome
                 - $this->totalExpenses 
                 - $this->totalStnkExpense
                 + $this->totalStnkMargin;

        return [
            // Existing
            ['type' => 'row', 'label' => __('tables.sale'),    'amount' => $this->totalSales],
            ['type' => 'row', 'label' => __('tables.income'),  'amount' => $this->totalIncomes],
            ['type' => 'row', 'label' => __('tables.expense'), 'amount' => $this->totalExpenses],

            // Tambahan STNK
            ['type' => 'row', 'label' => __('tables.stnk_income'),  'amount' => $this->totalStnkIncome],
            ['type' => 'row', 'label' => __('tables.stnk_expense'), 'amount' => $this->totalStnkExpense],
            ['type' => 'row', 'label' => __('tables.stnk_margin'),  'amount' => $this->totalStnkMargin],

            // Total
            ['type' => 'total', 'label' => __('tables.total'), 'profit' => $profit],
        ];
    }

    public function table(Table $table): Table
    {
        $fmt = fn (float $n) => number_format($n, 0, ',', '.');

        return $table
            ->records(fn () => $this->makeRows())
            ->paginated(false)
            ->columns([
                // Label
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
                        'class' => 'w-full' . (is_array($record) && ($record['type'] ?? null) !== 'total' 
                            ? ' uppercase tracking-wider' 
                            : '')
                    ]),

                // Nilai
                Tables\Columns\TextColumn::make('amount_display')
                    ->label('')
                    ->getStateUsing(function ($record) use ($fmt) {
                        if (!is_array($record)) return null;

                        if (($record['type'] ?? null) === 'total') {
                            $p = (float) ($record['profit'] ?? 0);
                            $sign = $p < 0 ? '-' : '';
                            return $sign . $fmt(abs($p)) . ' ' 
                                . ($p < 0 ? __('tables.loss') : __('tables.profit'));
                        }

                        return $fmt((float) ($record['amount'] ?? 0));
                    })
                    ->extraAttributes(fn ($record) => [
                        'class' => 'text-right tabular-nums' 
                            . (is_array($record) && ($record['type'] ?? null) === 'total' 
                                ? ((float) ($record['profit'] ?? 0) < 0 
                                    ? ' text-red-600' 
                                    : ' text-green-600') 
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
