<?php

namespace App\Filament\Resources\VideoSettings\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;

class VideoSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('youtube_url')
                ->label('YouTube URL')
                ->placeholder('https://www.youtube.com/watch?v=xxxxxxx')
                ->url()
                ->required()
                ->columnSpanFull(),
        ]);
    }
}
