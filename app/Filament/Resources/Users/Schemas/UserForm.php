<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('tables.name'))
                    ->required(),

                TextInput::make('email')
                    ->label(__('tables.email'))
                    ->email()
                    ->required(),

                DateTimePicker::make('email_verified_at')
                    ->label(__('tables.email_verified_at')),

                TextInput::make('password')
                    ->label(__('tables.password'))
                    ->password()
                    ->required(),

              Select::make('role')
                    ->options(['owner' => 'Owner', 'admin' => 'Admin', 'marketing' => 'Marketing'])
                    ->default('admin')
                    ->required(),
            ]);
    }
}
