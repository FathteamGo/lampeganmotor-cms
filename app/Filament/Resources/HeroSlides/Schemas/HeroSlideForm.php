<?php

namespace App\Filament\Resources\HeroSlides\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class HeroSlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('title')
                ->label('Judul')
                ->maxLength(255),

            Forms\Components\TextInput::make('subtitle')
                ->label('Sub Judul')
                ->maxLength(255),

            Forms\Components\FileUpload::make('image')
            ->disk('public')
            ->directory('hero-slides')
            ->image()
            ->required(),

        ]);
    }
}
