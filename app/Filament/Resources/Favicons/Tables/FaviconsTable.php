<?php

namespace App\Filament\Resources\Favicons\Tables;

use Filament\Actions\DeleteAction as ActionsDeleteAction;
use Filament\Actions\DeleteBulkAction as ActionsDeleteBulkAction;
use Filament\Actions\EditAction as ActionsEditAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;


class FaviconsTable
{
    public static function table(Table $table): Table
    {
        
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('Favicon')
                    ->disk('public')
                    ->rounded(),
                TextColumn::make('created_at')
                    ->label('Uploaded At')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionsEditAction::make(),
                ActionsDeleteAction::make(),
            ])
            ->bulkActions([
                ActionsDeleteBulkAction::make(),
            ]);
    }
}
