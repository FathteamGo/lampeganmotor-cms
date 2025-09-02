<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               Select::make('vehicle_id')
                ->label('Vehicle')
                ->options(
                    \App\Models\Vehicle::with(['vehicleModel', 'color'])
                        ->get()
                        ->mapWithKeys(fn($vehicle) => [
                            $vehicle->id => sprintf(
                                '%s | %s | %s',
                                $vehicle->vehicleModel->name ?? 'Unknown Model',
                                $vehicle->color->name ?? 'Unknown Color',
                                $vehicle->license_plate ?? 'No Plate'
                            ),
                        ])
                )
                ->searchable()
                ->required(),


               Select::make('customer_id')
                ->label('Customer')
                ->options(
                    \App\Models\Customer::all()
                        ->pluck('name', 'id')
                )
                ->searchable()
                ->required(),

                DatePicker::make('sale_date')
                    ->required(),
                TextInput::make('sale_price')
                    ->required()
                    ->numeric(),
                TextInput::make('payment_method')
                    ->required()
                    ->default('cash'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
