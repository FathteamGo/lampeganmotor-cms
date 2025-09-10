<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('tables.customers')) // multi-bahasa
                    ->searchable(),

                TextColumn::make('nik')
                    ->label(__('tables.nik'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                TextColumn::make('phone')
                    ->label(__('tables.phone'))
                    ->searchable(),

                TextColumn::make('address')
                    ->label(__('alamat'))
                    ->searchable(),

                TextColumn::make('instagram')
                    ->label('Instagram')
                    ->formatStateUsing(fn ($record) => $record->instagram ? '@'.$record->instagram : null)
                    ->url(fn ($record) => $record->instagram_url, shouldOpenInNewTab: true),

                TextColumn::make('tiktok')
                    ->label('TikTok')
                    ->formatStateUsing(fn ($record) => $record->tiktok ? '@'.$record->tiktok : null)
                    ->url(fn ($record) => $record->tiktok_url, shouldOpenInNewTab: true),

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
