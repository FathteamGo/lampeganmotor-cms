<?php

namespace App\Filament\Resources\CmoSummaries\Tables;

use App\Exports\AllCmoSummaryExport;
use App\Exports\CmoDetailExport;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\EditAction as ActionsEditAction;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class CmoSummariesTable
{
    public static function configure(Table $table): Table
    {
        $currentMonth = now()->month;
        $currentYear  = now()->year;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama CMO')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sales_count')
                    ->label('Total Transaksi')
                    ->alignCenter()
                    ->getStateUsing(fn ($record) =>
                        $record->sales()
                            ->whereNotIn('status', ['cancel'])
                            ->whereMonth(
                                'sale_date',
                                request('tableFilters.month.value', $currentMonth)
                            )
                            ->whereYear(
                                'sale_date',
                                request('tableFilters.year.value', $currentYear)
                            )
                            ->count()
                    ),

                Tables\Columns\TextColumn::make('total_fee')
                    ->label('Total Fee')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) =>
                        $record->sales()
                            ->whereNotIn('status', ['cancel'])
                            ->whereMonth(
                                'sale_date',
                                request('tableFilters.month.value', $currentMonth)
                            )
                            ->whereYear(
                                'sale_date',
                                request('tableFilters.year.value', $currentYear)
                            )
                            ->sum('cmo_fee')
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('month')
                    ->label('Bulan')
                    ->default($currentMonth)
                    ->options([
                        1 => 'Januari',
                        2 => 'Februari',
                        3 => 'Maret',
                        4 => 'April',
                        5 => 'Mei',
                        6 => 'Juni',
                        7 => 'Juli',
                        8 => 'Agustus',
                        9 => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember',
                    ])
                    ->query(fn (Builder $query, $state) =>
                        $query->whereHas('sales', fn ($q) =>
                            $q->whereMonth('sale_date', $state)
                        )
                    ),

                Tables\Filters\SelectFilter::make('year')
                    ->label('Tahun')
                    ->default($currentYear)
                    ->options(fn () =>
                        collect(range($currentYear, $currentYear - 5))
                            ->mapWithKeys(fn ($y) => [$y => $y])
                            ->toArray()
                    )
                    ->query(fn (Builder $query, $state) =>
                        $query->whereHas('sales', fn ($q) =>
                            $q->whereYear('sale_date', $state)
                        )
                    ),
            ])
            ->headerActions([
                Action::make('export_all')
                    ->label('Export Semua CMO')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(fn () =>
                        Excel::download(
                            new AllCmoSummaryExport(
                                request('tableFilters.month.value', $currentMonth),
                                request('tableFilters.year.value', $currentYear)
                            ),
                            'Rekap-Semua-CMO.xlsx'
                        )
                    ),
            ])
            ->actions([
                ActionsEditAction::make()
                    ->label('Edit'),

                Action::make('export')
                    ->label('Export Detail')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(fn ($record) =>
                        Excel::download(
                            new CmoDetailExport(
                                $record,
                                request('tableFilters.month.value', $currentMonth),
                                request('tableFilters.year.value', $currentYear)
                            ),
                            'CMO-' . str($record->name)->slug() . '.xlsx'
                        )
                    ),
            ])
            ->defaultSort('name');
    }
}
