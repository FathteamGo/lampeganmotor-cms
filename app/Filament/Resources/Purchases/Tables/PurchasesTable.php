<?php

namespace App\Filament\Resources\Purchases\Tables;

use Dom\Text;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle.vehicleModel.name')
                    ->label('Model')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->sortable()
                    ->searchable(),


                TextColumn::make('vehicle.license_plate')
                    ->label('Nomor Polisi')
                    ->searchable(),

                TextColumn::make('purchase_date')
                    ->date()
                    ->label('Tanggal Pembelian')
                    ->sortable(),


                TextColumn::make('vehicle.purchase_price')
                    ->label('Harga Beli Motor')
                    ->formatStateUsing(fn ($record) =>
                        'Rp ' . number_format(
                            $record->vehicle->purchase_price ?? 0,
                            0,
                            ',',
                            '.'
                        )
                    )
                    ->sortable(),

                TextColumn::make('vehicle.sale_price')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn ($record) =>
                        'Rp ' . number_format(
                            $record->vehicle->sale_price ?? 0,
                            0,
                            ',',
                            '.'
                        )
                    )
                    ->sortable(),

                TextColumn::make('total_purchase')
                    ->label('Total Pembelian')
                    ->state(function ($record) {
                        return 'Rp ' . number_format(
                            ($record->vehicle?->purchase_price ?? 0)
                            + ($record->additionalCosts?->sum('price') ?? 0),
                            0,
                            ',',
                            '.'
                        );
                    }),


                    

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Dibuat')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Diperbarui')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->label('Lihat'),
                EditAction::make()->label('Ubah'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus'),
                ]),
            ]);
    }


}
