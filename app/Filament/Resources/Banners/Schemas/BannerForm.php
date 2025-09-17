<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
              Forms\Components\TextInput::make('title')
                ->required(),
           Forms\Components\FileUpload::make('image')
                ->label('Gambar Banner')
                ->disk('public')
                ->image()
                ->required()
                ->maxSize(4096)
                ->imagePreviewHeight('250'),
            Forms\Components\DatePicker::make('start_date')
                ->required(),
            Forms\Components\DatePicker::make('end_date')
                ->required(),
            Forms\Components\Toggle::make('is_active')
                ->label('Active')
                ->default(true),
            ]);
    }
}
