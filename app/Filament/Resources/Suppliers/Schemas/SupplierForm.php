<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('navigation.supplier')) 
                    ->required(),

                TextInput::make('dealer')
                    ->label(__('tables.dealer'))
                    ->required(),

                TextInput::make('phone')
                    ->label(__('tables.phone'))
                    ->tel(),
                
                Textarea::make('address')
                    ->label(__('tables.address')) 
                    ->columnSpanFull(),
            ]);
    }
}
