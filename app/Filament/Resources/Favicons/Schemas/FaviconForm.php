<?php

namespace App\Filament\Resources\Favicons\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;

class FaviconForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\FileUpload::make('path')
                ->label('Favicon')
                ->image()
                ->disk('public') // simpan di storage/app/public
                ->directory('favicons')
                ->visibility('public')
                ->required(),
        ]);
    }
}
