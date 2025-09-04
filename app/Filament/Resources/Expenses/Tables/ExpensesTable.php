<?php

namespace App\Filament\Resources\Expenses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label(__('tables.description')) // multi-bahasa
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label(__('tables.category')) // multi-bahasa
                    ->sortable()
                    ->searchable(),

                TextColumn::make('amount')
                    ->label(__('tables.amount')) // multi-bahasa
                    ->numeric()
                    ->sortable(),

                TextColumn::make('expense_date')
                    ->label(__('tables.expense_date')) // multi-bahasa
                    ->date()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('tables.created_at')) // multi-bahasa
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('tables.updated_at')) // multi-bahasa
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label(__('tables.view')), // multi-bahasa
                EditAction::make()
                    ->label(__('tables.edit')), // multi-bahasa
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('tables.delete')), // multi-bahasa
                ]),
            ]);
    }
}
