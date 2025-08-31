<?php

namespace App\Filament\Resources\Years\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class YearForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('year')
                    ->required(),
            ]);
    }
}
