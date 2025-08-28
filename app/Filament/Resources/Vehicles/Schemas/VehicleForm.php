<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('vehicle_model_id')
                    ->required()
                    ->numeric(),
                TextInput::make('type_id')
                    ->required()
                    ->numeric(),
                TextInput::make('color_id')
                    ->required()
                    ->numeric(),
                TextInput::make('year_id')
                    ->required()
                    ->numeric(),
                TextInput::make('vin')
                    ->required(),
                TextInput::make('engine_number')
                    ->required(),
                TextInput::make('license_plate'),
                TextInput::make('bpkb_number'),
                TextInput::make('purchase_price')
                    ->required()
                    ->numeric(),
                TextInput::make('sale_price')
                    ->numeric(),
                Select::make('status')
                    ->options(['available' => 'Available', 'sold' => 'Sold', 'in_repair' => 'In repair', 'hold' => 'Hold'])
                    ->default('hold')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
