<?php

namespace App\Filament\Resources\VideoSettings\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class VideoSettingsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('youtube_url')
                    ->label('YouTube URL')
                    ->limit(50),
                TextColumn::make('updated_at')
                    ->label('Terakhir Update')
                    ->since(),
            ]);
    }
}
