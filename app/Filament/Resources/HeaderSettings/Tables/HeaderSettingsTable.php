<?php

namespace App\Filament\Resources\HeaderSettings\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class HeaderSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('site_name')->label('Nama Situs'),
            ImageColumn::make('logo')->label('Logo'),
            TextColumn::make('instagram_url')->label('Instagram'),
            TextColumn::make('tiktok_url')->label('TikTok'),
        ]);
    }
}
