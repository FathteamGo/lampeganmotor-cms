<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('tables.customer')) // label dari lang file
                    ->required(),

                TextInput::make('nik')
                    ->label(__('tables.nik')),

                TextInput::make('phone')
                    ->label(__('tables.phone'))
                    ->tel(),

                Textarea::make('address')
                    ->label(__('tables.address')) // jangan lupa tambahkan 'address' di lang
                    ->columnSpanFull(),
            ]);
    }
}
