<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BrandForm
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
