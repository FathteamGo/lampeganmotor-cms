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
                // === NAMA SALES ===
                TextColumn::make('name')
                    ->label('Nama Sales')
                    ->sortable()
                    ->searchable(),

                // === UNIT TERJUAL ===
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

                // === TOTAL OMZET ===
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

                // === BONUS ===
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

                // === GAJI POKOK ===
                TextColumn::make('base_salary')
                    ->label('Gaji Pokok')
                    ->money('IDR', true)
                    ->getStateUsing(fn($record) => $record->base_salary ?? 0),

                // === TOTAL PENGHASILAN ===
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

            // === FILTER PERIODE ===
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

            // === EXPORT KE EXCEL ===
            ->headerActions([
                ActionsAction::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-s-arrow-down-tray')
                    ->action(function ($data, $livewire) {
                        /** 
                         * NOTE: Di Filament 3, $livewire->filters adalah cara resmi
                         * untuk ambil filter aktif dari tabel
                         */
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

    /**
     * Hitung bonus otomatis sesuai kebijakan.
     */
    private static function calculateBonus(int $salesCount): int
    {
        if ($salesCount <= 0) return 0;

        // 1–4 unit → 150k per unit
        if ($salesCount < 5) return 150_000 * $salesCount;

        // 5–9 unit → 250k per unit
        if ($salesCount < 10) return 250_000 * $salesCount;

        // 10 unit → 250k per unit + 500k total
        if ($salesCount == 10) return (250_000 * 10) + 500_000;

        // Di atas 11 unit → 250k per unit untuk 10 pertama + 500k + 150k sisanya
        return (250_000 * 10) + 500_000 + (150_000 * ($salesCount - 10));
    }
}
