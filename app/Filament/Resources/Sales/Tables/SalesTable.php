<?php

namespace App\Filament\Resources\Sales\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle.vehicleModel.name')
                    ->label(__('tables.model'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('vehicle.color.name')
                    ->label(__('tables.color'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('vehicle.license_plate')
                    ->label(__('tables.license_plate'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('customer.name')
                    ->label(__('tables.customer'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('sale_date')
                    ->date()
                    ->label(__('tables.sale_date'))
                    ->sortable(),

                TextColumn::make('sale_price')
                    ->numeric()
                    ->label(__('tables.sale_price'))
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label(__('tables.payment_method'))
                    ->searchable(),

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
            ->filters([
                //
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
