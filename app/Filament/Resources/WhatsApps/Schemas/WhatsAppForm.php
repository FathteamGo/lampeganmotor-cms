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
          Forms\Components\Select::make('user_id')
    ->label('User')
    ->relationship('user', 'name')
    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' (' . $record->role . ')')
    ->searchable()
    ->preload()
    ->required(),

    Forms\Components\TextInput::make('name')
        ->label('Alias / Nama Nomor')   // <-- diganti biar tidak "User"
        ->required(),

    Forms\Components\TextInput::make('number')
        ->label('Nomor WA')
        ->required(),

    Forms\Components\Toggle::make('is_active')
        ->label('Aktif')
        ->default(true),


            ]);
    }
}
