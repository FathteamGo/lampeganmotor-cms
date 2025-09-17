<?php

namespace App\Filament\Resources\WhatsApps\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class WhatsAppsTable
{
    public static function configure(Table $table): Table
    {
        return $table
           ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->limit(50),
                TextColumn::make('number')
                    ->label('Nomor WA')
                    ->limit(20),
                ToggleColumn::make('is_active')
                    ->label('Aktif')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ]);
            // ->toolbarActions([
            //     BulkActionGroup::make([
            //         DeleteBulkAction::make(),
            //     ]),
            
    }
}
