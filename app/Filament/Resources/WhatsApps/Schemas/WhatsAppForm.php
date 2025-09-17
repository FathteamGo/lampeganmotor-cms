<?php

namespace App\Filament\Resources\WhatsApps\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms;

class WhatsAppForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
              Forms\Components\TextInput::make('name')
                ->label('Nama')
                ->required(),
              Forms\Components\TextInput::make('number')
                ->label('Nomor Wa')
                ->required(),
              Forms\Components\Toggle::make('is_active')
                ->label('Is Active')
                ->default(true)
        ]);
    }
}
