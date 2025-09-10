<?php

namespace App\Filament\Resources\Sales\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.address')
                    ->label('Alamat')
                    ->toggleable(),

                TextColumn::make('customer.phone')
                    ->label('No Telepon')
                    ->toggleable(),

                TextColumn::make('vehicle.vehicleModel.name')
                    ->label('Jenis Motor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vehicle.type.name')
                    ->label('Type')
                    ->toggleable(),

                TextColumn::make('vehicle.year.year')
                    ->label('Tahun')
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('vehicle.color.name')
                    ->label('Warna')
                    ->toggleable(),

                TextColumn::make('vehicle.license_plate')
                    ->label('No Pol')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vehicle.purchase_price')
                    ->label('H-Total Pembelian')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('vehicle.sale_price')
                    ->label('OTR')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('dp_po')
                    ->label('Dp Po')
                    ->state(fn ($record) => optional($record->vehicle)->dp_percentage
                        ? round((($record->vehicle->sale_price ?? $record->sale_price ?? 0) * ($record->vehicle->dp_percentage / 100)))
                        : null
                    )
                    ->money('IDR', locale: 'id')
                    ->toggleable(),

                TextColumn::make('dp_real')
                    ->label('Dp Real')
                    ->state(fn ($record) => $record->payment_method === 'cash_tempo'
                        ? max(0, (int) ($record->sale_price - (int) ($record->remaining_payment ?? 0)))
                        : null
                    )
                    ->money('IDR', locale: 'id')
                    ->toggleable(),

                TextColumn::make('pencairan')
                    ->label('Pencairan')
                    ->money('IDR', locale: 'id')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('sale_price')
                    ->label('Total Penjualan')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('laba_bersih')
                    ->label('Laba Bersih')
                    ->money('IDR', locale: 'id')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('payment_method')
                    ->label(__('tables.payment_method'))
                    ->searchable(),

                TextColumn::make('cmo')
                    ->label('CMO')
                    ->toggleable(),

                TextColumn::make('cmo_fee')
                    ->label('FEE CMO')
                    ->money('IDR', locale: 'id')
                    ->toggleable(),

                TextColumn::make('order_source')
                    ->label(__('tables.order_source'))
                    ->searchable(),

                TextColumn::make('komisi_langsung')
                    ->label('Komisi')
                    ->money('IDR', locale: 'id')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Ex')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('branch_name')
                    ->label('Cabang')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('result')
                    ->label('Hasil')
                    ->formatStateUsing(fn ($s) => $s ?: '-')
                    ->badge()
                    ->color(fn ($s) => match ($s) {
                        'CASH' => 'success',
                        'TT'   => 'info',
                        'ACC'  => 'primary',
                        'X'    => 'gray',
                        default => 'secondary',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('grand_total')
                    ->label('Total')
                    ->state(fn ($record) => $record->sale_price)
                    ->money('IDR', locale: 'id')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(__('tables.status'))
                    ->searchable(),

                TextColumn::make('sale_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Note')
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label(__('tables.created_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->label(__('tables.updated_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make()->label(__('tables.view')),
                EditAction::make()->label(__('tables.edit')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label(__('tables.delete')),
                ]),
            ]);
    }
}
