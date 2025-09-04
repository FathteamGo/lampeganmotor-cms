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
                    ->label(__('tables.purchase_model'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('supplier.name')
                    ->label(__('tables.purchase_supplier'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('purchase_date')
                    ->date()
                    ->label(__('tables.purchase_date'))
                    ->sortable(),

                TextColumn::make('total_price')
                    ->numeric()
                    ->prefix('Rp')
                    ->label(__('tables.purchase_total_price'))
                    ->sortable(),

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
                ViewAction::make()
                    ->label(__('tables.view')),
                EditAction::make()
                    ->label(__('tables.edit')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('tables.delete')),
                ]),
            ]);
    }
}
