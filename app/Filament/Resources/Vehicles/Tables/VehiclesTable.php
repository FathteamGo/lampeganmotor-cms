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
                TextColumn::make('vehicleModel.name')
                    ->label(__('tables.model'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type.name')
                    ->label(__('tables.type'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('color.name')
                    ->label(__('tables.color'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('year.year')
                    ->label(__('tables.year'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('vin')
                    ->label(__('tables.vin'))
                    ->searchable(),

                TextColumn::make('engine_number')
                    ->label(__('tables.engine_number'))
                    ->searchable(),

                TextColumn::make('license_plate')
                    ->label(__('tables.license_plate'))
                    ->searchable(),

                TextColumn::make('bpkb_number')
                    ->label(__('tables.bpkb_number'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('purchase_price')
                    ->label(__('tables.purchase_price'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('sale_price')
                    ->label(__('tables.sale_price'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('odometer')
                    ->label(__('tables.odometer'))
                    ->numeric()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label(__('tables.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'sold'      => 'danger',
                        'in_repair' => 'warning',
                        'hold'      => 'gray',
                        default     => 'gray',
                    })
                    ->searchable(),

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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Available',
                        'sold'      => 'Sold',
                        'in_repair' => 'In Repair',
                        'hold'      => 'Hold',
                    ]),
            ])
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
