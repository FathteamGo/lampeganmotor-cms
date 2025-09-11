<?php

namespace App\Filament\Resources\HeaderSettings\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;

class HeaderSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('site_name')
                ->label('Nama Situs')
                ->required(),

           FileUpload::make('logo')
            ->label('Logo')
            ->image()
            ->directory('logos')
            ->disk('public'),


            TextInput::make('instagram_url')
                ->label('Instagram URL')
                ->url(),

            TextInput::make('tiktok_url')
                ->label('TikTok URL')
                ->url(),
        ]);
    }
}
