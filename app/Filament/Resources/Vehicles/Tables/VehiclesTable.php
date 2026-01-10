<?php

namespace App\Filament\Resources\Vehicles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_index')
                    ->label('No')
                    ->rowIndex()
                    ->alignCenter(),

                TextColumn::make('vehicleModel.name')
                    ->label(__('tables.model'))
                    ->sortable()
                    ->searchable()
                    ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                    ->color(fn($record) => $record->status === 'sold' ? 'danger' : null),

                TextColumn::make('type.name')
                    ->label(__('tables.type'))
                    ->sortable()
                    ->searchable()
                    ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                    ->color(fn($record) => $record->status === 'sold' ? 'danger' : null),

                TextColumn::make('color.name')
                    ->label(__('tables.color'))
                    ->sortable()
                    ->searchable()
                    ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                    ->color(fn($record) => $record->status === 'sold' ? 'danger' : null),

                TextColumn::make('year.year')
                    ->label(__('tables.year'))
                    ->sortable()
                    ->searchable()
                    ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                    ->color(fn($record) => $record->status === 'sold' ? 'danger' : null),

                TextColumn::make('vin')
                    ->label(__('tables.vin'))
                    ->searchable()
                    ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                    ->color(fn($record) => $record->status === 'sold' ? 'danger' : null),

                TextColumn::make('engine_number')
                    ->label(__('tables.engine_number'))
                    ->searchable()
                    ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                    ->color(fn($record) => $record->status === 'sold' ? 'danger' : null),

                TextColumn::make('license_plate')
                    ->label(__('tables.license_plate'))
                    ->searchable()
                    ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                    ->color(fn($record) => $record->status === 'sold' ? 'danger' : null),

                TextColumn::make('bpkb_number')
                    ->label(__('tables.bpkb_number'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                    ->color(fn($record) => $record->status === 'sold' ? 'danger' : null),

                // HARGA BELI MOTOR (Purchase Price saja)
                TextColumn::make('purchase_price')
                    ->label('Harga Beli Motor')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                    ->color(fn($record) => $record->status === 'sold' ? 'danger' : null),

                // TOTAL HARGA PEMBELIAN (Purchase Price + Rekondisi)
                 TextColumn::make('total_purchase')
                    ->label('Total Pembelian')
                    ->state(function ($record) {
                        return 'Rp ' . number_format(
                            ($record->purchase_price ?? 0)
                            + ($record->purchaseadditionalCosts?->sum('price') ?? 0),
                            0,
                            ',',
                            '.'
                        );
                    })
                    ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                    ->color(fn($record) => $record->status === 'sold' ? 'danger' : null),

                TextColumn::make('sale_price')
                    ->label('Harga Jual')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                    ->color(fn($record) => $record->status === 'sold' ? 'danger' : null),

                TextColumn::make('odometer')
                    ->label(__('tables.odometer'))
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                    ->color(fn($record) => $record->status === 'sold' ? 'danger' : null),


                // TextColumn::make('stock')
                //     ->label('Stok')
                //     ->badge()
                //     ->getStateUsing(fn($record) => $record->status === 'sold' ? 0 : 1)
                //     ->color(fn($state) => $state === 0 ? 'danger' : 'success')
                //     ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                //     ->description('0 = Terjual, 1 = Tersedia'),

                TextColumn::make('status')
                    ->label(__('tables.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'sold'      => 'danger',
                        'in_repair' => 'info',
                        'hold'      => 'gray',
                        default     => 'gray',
                    })
                    ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label(__('tables.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                    ->color(fn($record) => $record->status === 'sold' ? 'danger' : null),

                TextColumn::make('updated_at')
                    ->label(__('tables.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->weight(fn($record) => $record->status === 'sold' ? 'bold' : 'normal')
                    ->color(fn($record) => $record->status === 'sold' ? 'danger' : null),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Available',
                        'sold'      => 'Sold',
                        'in_repair' => 'In Repair',
                        'hold'      => 'Hold',
                    ])
                    ->multiple(),
            ])
            ->recordActions([
                ViewAction::make()->label(__('tables.view')),
                EditAction::make()->label(__('tables.edit')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label(__('tables.delete')),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginationPageOptions([10, 25, 50, 100, 200]);
    }
}
