<?php

namespace App\Filament\Resources\Types\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TypeForm
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
