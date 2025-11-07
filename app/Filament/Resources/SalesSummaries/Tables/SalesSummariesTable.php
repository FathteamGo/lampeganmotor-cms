<?php

namespace App\Filament\Resources\SalesSummaries\Tables;

use App\Models\User;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesSummaryExport;
use Filament\Actions\Action as ActionsAction;

class SalesSummariesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(User::query())
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Sales')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('sales_count')
                    ->label('Unit Terjual')
                    ->getStateUsing(function ($record, $column) {
                        $filters = $column->getTable()->getFiltersForm()->getState()['periode'] ?? [];
                        $month = $filters['month'] ?? now()->format('m');
                        $year  = $filters['year'] ?? now()->format('Y');

                        return DB::table('sales')
                            ->where('user_id', $record->id)
                            ->whereNotIn('status', ['cancel'])
                            ->whereIn('result', ['ACC', 'CASH'])
                            ->whereMonth('sale_date', $month)
                            ->whereYear('sale_date', $year)
                            ->count();
                    }),

                TextColumn::make('total_omzet')
                    ->label('Total Omzet')
                    ->money('IDR', true)
                    ->getStateUsing(function ($record, $column) {
                        $filters = $column->getTable()->getFiltersForm()->getState()['periode'] ?? [];
                        $month = $filters['month'] ?? now()->format('m');
                        $year  = $filters['year'] ?? now()->format('Y');

                        return DB::table('sales')
                            ->where('user_id', $record->id)
                            ->whereNotIn('status', ['cancel'])
                            ->whereIn('result', ['ACC', 'CASH'])
                            ->whereMonth('sale_date', $month)
                            ->whereYear('sale_date', $year)
                            ->sum('sale_price');
                    }),

                TextColumn::make('bonus')
                    ->label('Bonus')
                    ->money('IDR', true)
                    ->getStateUsing(function ($record, $column) {
                        $filters = $column->getTable()->getFiltersForm()->getState()['periode'] ?? [];
                        $month = $filters['month'] ?? now()->format('m');
                        $year  = $filters['year'] ?? now()->format('Y');

                        $salesCount = DB::table('sales')
                            ->where('user_id', $record->id)
                            ->whereNotIn('status', ['cancel'])
                            ->whereIn('result', ['ACC', 'CASH'])
                            ->whereMonth('sale_date', $month)
                            ->whereYear('sale_date', $year)
                            ->count();

                        return self::calculateBonus($salesCount);
                    }),

                TextColumn::make('base_salary')
                    ->label('Gaji Pokok')
                    ->money('IDR', true)
                    ->getStateUsing(fn($record) => $record->base_salary ?? 0),

                TextColumn::make('total_income')
                    ->label('Total Penghasilan')
                    ->money('IDR', true)
                    ->getStateUsing(function ($record, $column) {
                        $filters = $column->getTable()->getFiltersForm()->getState()['periode'] ?? [];
                        $month = $filters['month'] ?? now()->format('m');
                        $year  = $filters['year'] ?? now()->format('Y');

                        $salesCount = DB::table('sales')
                            ->where('user_id', $record->id)
                            ->whereNotIn('status', ['cancel'])
                            ->whereIn('result', ['ACC', 'CASH'])
                            ->whereMonth('sale_date', $month)
                            ->whereYear('sale_date', $year)
                            ->count();

                        $bonus = self::calculateBonus($salesCount);
                        return ($record->base_salary ?? 0) + $bonus;
                    }),
            ])

            ->filters([
                Filter::make('periode')
                    ->form([
                        Select::make('month')
                            ->label('Bulan')
                            ->options([
                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                            ])
                            ->default(now()->format('m')),

                        Select::make('year')
                            ->label('Tahun')
                            ->options(fn() => DB::table('sales')
                                ->selectRaw('YEAR(sale_date) as year')
                                ->whereNotIn('status', ['cancel'])
                                ->groupBy('year')
                                ->orderBy('year', 'desc')
                                ->pluck('year', 'year')
                                ->toArray()
                            )
                            ->default(now()->format('Y')),
                    ]),
            ])

            ->headerActions([
                ActionsAction::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-s-arrow-down-tray')
                    ->action(function ($data, $livewire) {
                        $filters = $livewire->filters['periode'] ?? [];
                        $month = $filters['month'] ?? now()->format('m');
                        $year  = $filters['year'] ?? now()->format('Y');

                        return Excel::download(
                            new SalesSummaryExport($month, $year),
                            "sales_summary_{$month}_{$year}.xlsx"
                        );
                    }),
            ])
            ->defaultSort('name', 'asc');
    }

    private static function calculateBonus(int $salesCount): int
    {
        if ($salesCount <= 0) return 0;
        if ($salesCount < 5) return 150_000 * $salesCount;
        if ($salesCount < 10) return 250_000 * $salesCount;
        if ($salesCount == 10) return (250_000 * 10) + 500_000;
        return (250_000 * 10) + 500_000 + (150_000 * ($salesCount - 10));
    }
}
