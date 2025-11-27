<?php

namespace App\Filament\Resources\CashTempoTrackings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CashTempoTrackingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('sale_date')
                    ->label('Tgl Penjualan')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('customer.phone')
                    ->label('No. Telepon')
                    ->searchable(),

                TextColumn::make('vehicle.vehicleModel.name')
                    ->label('Motor')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('vehicle.license_plate')
                    ->label('No Pol')
                    ->searchable(),

                TextColumn::make('sale_price')
                    ->label('OTR')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('dp_po')
                    ->label('DP PO')
                    ->money('IDR', locale: 'id'),

                TextColumn::make('dp_real')
                    ->label('DP REAL')
                    ->money('IDR', locale: 'id'),

                TextColumn::make('remaining_payment')
                    ->label('Sisa Pembayaran')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->color('warning')
                    ->weight('bold')
                    ->description('Uang mengendap'),

                TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn($record) => 
                        $record->due_date && $record->due_date <= now() 
                            ? 'danger' 
                            : ($record->due_date && $record->due_date <= now()->addDays(7) 
                                ? 'warning' 
                                : 'success')
                    )
                    ->icon(fn($record) => 
                        $record->due_date && $record->due_date <= now() 
                            ? 'heroicon-o-exclamation-triangle' 
                            : null
                    ),

                TextColumn::make('hari_tersisa')
                    ->label('Hari Tersisa')
                    ->state(function ($record) {
                        if (!$record->due_date) return '-';
                        
                        // ✅ FIX: Pakai startOfDay() biar dapat integer bukan float
                        $now = now()->startOfDay();
                        $dueDate = $record->due_date->startOfDay();
                        $days = $now->diffInDays($dueDate, false);
                        
                        if ($days < 0) {
                            return abs($days) . ' hari terlambat';
                        } elseif ($days == 0) {
                            return 'Hari ini';
                        } else {
                            return $days . ' hari lagi';
                        }
                    })
                    ->badge()
                    ->color(fn($record) => 
                        !$record->due_date ? 'gray' : (
                            now()->startOfDay()->diffInDays($record->due_date->startOfDay(), false) < 0 
                                ? 'danger' 
                                : (now()->startOfDay()->diffInDays($record->due_date->startOfDay(), false) <= 7 
                                    ? 'warning' 
                                    : 'success')
                        )
                    ),

                TextColumn::make('user.name')
                    ->label('Sales')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'proses' => 'info',
                        'kirim' => 'success',
                        'selesai' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'proses' => 'Proses',
                        'kirim' => 'Kirim',
                        'selesai' => 'Selesai',
                    ]),

                Filter::make('jatuh_tempo')
                    ->label('Sudah Jatuh Tempo')
                    ->query(function (Builder $query): Builder {
                        return $query->whereNotNull('due_date')
                            ->where('due_date', '<=', now());
                    })
                    ->toggle(),

                Filter::make('jatuh_tempo_7_hari')
                    ->label('Jatuh Tempo 7 Hari')
                    ->query(function (Builder $query): Builder {
                        return $query->whereNotNull('due_date')
                            ->whereBetween('due_date', [now(), now()->addDays(7)]);
                    })
                    ->toggle(),

                Filter::make('jatuh_tempo_30_hari')
                    ->label('Jatuh Tempo 30 Hari')
                    ->query(function (Builder $query): Builder {
                        return $query->whereNotNull('due_date')
                            ->whereBetween('due_date', [now(), now()->addDays(30)]);
                    })
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_date', 'asc')
            ->poll('30s');
    }
}