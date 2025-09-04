<?php

namespace App\Filament\Resources\Incomes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IncomesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label(__('tables.description'))
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label(__('tables.category'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('amount')
                    ->numeric()
                    ->sortable()
                    ->label(__('tables.amount')),

                TextColumn::make('income_date')
                    ->date()
                    ->sortable()
                    ->label(__('tables.income_date')),

                TextColumn::make('customer.name')
                    ->label(__('tables.customer'))
                    ->sortable()
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
