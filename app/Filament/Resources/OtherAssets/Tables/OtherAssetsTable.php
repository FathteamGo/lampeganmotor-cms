<?php

namespace App\Filament\Resources\OtherAssets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OtherAssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('tables.name'))
                    ->searchable(),

                TextColumn::make('value')
                    ->numeric()
                    ->sortable()
                    ->label(__('tables.value')),

                TextColumn::make('acquisition_date')
                    ->date()
                    ->sortable()
                    ->label(__('tables.acquisition_date')),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('tables.created_at')),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('tables.updated_at')),
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
