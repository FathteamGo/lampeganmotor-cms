<?php

namespace App\Filament\Resources\Colors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ColorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1) 
            ->schema([
                TextInput::make('name')
                    ->label(__('tables.name'))  // multi-bahasa
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
