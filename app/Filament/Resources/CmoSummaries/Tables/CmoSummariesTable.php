<?php

namespace App\Filament\Resources\CmoSummaries\Tables;

use App\Exports\CmoReportExport;
use App\Exports\AllCmoSummaryExport;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;

class CmoSummariesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama CMO')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sales_count')
                    ->label('Total Customer')
                    ->alignCenter()
                    ->getStateUsing(fn ($record) =>
                        $record->sales()->count()
                    ),

                Tables\Columns\TextColumn::make('total_fee')
                    ->label('Total Fee')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) =>
                        $record->sales()->sum('cmo_fee')
                    ),
            ])
            ->headerActions([
                Action::make('export_all')
                    ->label('Export Semua CMO')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(fn () =>
                        Excel::download(
                            new AllCmoSummaryExport(),
                            'Rekap-Semua-CMO.xlsx'
                        )
                    ),
            ])
            ->actions([
                Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn ($record) =>
                        Excel::download(
                            new CmoReportExport($record),
                            'CMO-' . str($record->name)->slug() . '.xlsx'
                        )
                    ),
            ])

            ->defaultSort('name');
    }
}
