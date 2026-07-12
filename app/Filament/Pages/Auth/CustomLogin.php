<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class CustomLogin extends Login
{
    protected static string $view = 'filament.pages.auth.custom-login';

    public function form(Form $form): Form
    {
        return $form
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
