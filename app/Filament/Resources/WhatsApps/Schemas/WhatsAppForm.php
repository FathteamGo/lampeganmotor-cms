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

    Forms\Components\Toggle::make('is_report_gateway')
        ->label('Gunakan untuk Report Gateway')
        ->reactive()
        ->afterStateUpdated(function ($state, $record) {
            if ($state) {
                // Reset semua record lain jadi false
                \App\Models\WhatsAppNumber::where('id', '!=', $record->id)
                    ->update(['is_report_gateway' => false]);
            }
        }),



            ]);
    }
}
