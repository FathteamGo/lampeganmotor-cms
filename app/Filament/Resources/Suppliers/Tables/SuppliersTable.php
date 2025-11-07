<?php

namespace App\Filament\Resources\Suppliers\Tables;

use App\Models\Supplier;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn() => Supplier::whereHas('purchases', function ($q) {
                $q->whereNotNull('vehicle_id');
            }))
            ->columns([
                TextColumn::make('name')
                    ->label(__('tables.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('dealer')
                    ->label(__('tables.dealer'))
                    ->searchable(),

                TextColumn::make('phone')
                    ->label(__('tables.phone'))
                    ->searchable(),

                // ===== JUMLAH UNIT DIPASOK =====
                TextColumn::make('total_units')
                    ->label('Total Unit Dipasok')
                    ->getStateUsing(fn ($record) => $record->purchases()->whereNotNull('vehicle_id')->count())
                    ->badge()
                    ->color('primary'),

                // ===== PASOKAN TERAKHIR =====
                TextColumn::make('last_supply')
                    ->label('Pasokan Terakhir')
                    ->getStateUsing(function ($record) {
                        $lastPurchase = $record->purchases()->latest('purchase_date')->first();
                        return $lastPurchase ? $lastPurchase->purchase_date->format('d M Y') : '-';
                    }),

                TextColumn::make('created_at')
                    ->label(__('tables.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('tables.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // ===== FILTER BULAN =====
                SelectFilter::make('month')
                    ->label('Bulan')
                    ->options([
                        '1'  => 'Januari',
                        '2'  => 'Februari',
                        '3'  => 'Maret',
                        '4'  => 'April',
                        '5'  => 'Mei',
                        '6'  => 'Juni',
                        '7'  => 'Juli',
                        '8'  => 'Agustus',
                        '9'  => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ])
                    ->default(now()->month) // default bulan sekarang
                    ->query(function ($query, array $data) {
                        if (!empty($data['value'])) {
                            return $query->whereHas('purchases', function ($sub) use ($data) {
                                $sub->whereMonth('purchase_date', $data['value'])
                                    ->whereNotNull('vehicle_id');
                            });
                        }
                        return $query;
                    }),

                // ===== FILTER TAHUN =====
                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = [];
                        for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                            $years[$i] = $i;
                        }
                        return $years;
                    })
                    ->default(now()->year) // default tahun sekarang
                    ->query(function ($query, array $data) {
                        if (!empty($data['value'])) {
                            return $query->whereHas('purchases', function ($sub) use ($data) {
                                $sub->whereYear('purchase_date', $data['value'])
                                    ->whereNotNull('vehicle_id');
                            });
                        }
                        return $query;
                    }),
            ])
            ->defaultSort('name', 'asc')
            ->recordActions([
                EditAction::make()->label(__('tables.edit')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label(__('tables.delete')),
                ]),
            ]);
    }
}
