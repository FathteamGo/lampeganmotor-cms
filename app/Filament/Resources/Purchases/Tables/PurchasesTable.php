<?php

namespace App\Filament\Resources\Purchases\Tables;

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

                TextColumn::make('purchase_date')
                    ->date()
                    ->label('Tanggal Pembelian')
                    ->sortable(),

                // total harga = harga motor + biaya tambahan
                TextColumn::make('grand_total')
                    ->label('Total Harga')
                    ->formatStateUsing(fn ($record) =>
                        'Rp ' . number_format(
                            ($record->total_price ?? 0) + ($record->additionalCosts->sum('price') ?? 0),
                            0,
                            ',',
                            '.'
                        )
                    )
                    ->sortable(),

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
