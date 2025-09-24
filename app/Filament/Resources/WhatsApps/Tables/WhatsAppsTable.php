<?php

namespace App\Filament\Resources\WhatsApps\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class WhatsAppsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
    TextColumn::make('user.name')
        ->label('User')
        ->sortable()
        ->searchable(),

    TextColumn::make('user.role')
        ->label('Role')
        ->badge()
        ->color(fn (string $state): string => match ($state) {
            'owner' => 'success',
            'admin' => 'info',
            default => 'gray',
        }),

    TextColumn::make('name')
        ->label('Alias')
        ->limit(50),

    TextColumn::make('number')
        ->label('Nomor WA')
        ->limit(20),

    ToggleColumn::make('is_active')
        ->label('Aktif'),

    IconColumn::make('is_report_gateway')
    ->boolean()
    ->label('Report Gateway'),

])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
