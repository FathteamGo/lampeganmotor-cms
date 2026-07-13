<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomLogin extends Login
{
    protected string $view = 'filament.pages.auth.custom-login';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('email')
                    ->label('Alamat email')
                    ->email()
                    ->required()
                    ->autocomplete()
                    ->autofocus(),

                TextInput::make('password')
                    ->label('Kata sandi')
                    ->password()
                    ->required()
                    ->revealable()
                    ->autocomplete('current-password'),

                Checkbox::make('remember')
                    ->label('Ingat saya'),
            ]);
    }
}
