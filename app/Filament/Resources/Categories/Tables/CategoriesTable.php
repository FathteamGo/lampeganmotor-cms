<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('tables.category_name')) 
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('tables.type')), 
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
                EditAction::make()
                    ->label(__('tables.edit')), 
                DeleteAction::make()
                    ->label(__('tables.delete'))
                    ->visible(fn () => Filament::auth()->user()?->role === 'owner'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('tables.delete'))
                        ->visible(fn () => Filament::auth()->user()?->role === 'owner'),
                ]),
            ]);
    }
}
