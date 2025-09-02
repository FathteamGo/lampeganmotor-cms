<?php

namespace App\Filament\Resources\Requests\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\Supplier;
use App\Models\Brand;
use App\Models\VehicleModel;
use App\Models\Year;

class RequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // Supplier
            Select::make('supplier_id')
                ->label('Supplier')
                ->options(Supplier::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),

            // Brand
            Select::make('brand_id')
                ->label('Brand')
                ->options(Brand::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required()
                ->reactive(),

            // Vehicle Model (filtered by brand)
            Select::make('vehicle_model_id')
                ->label('Model')
                ->options(fn ($get) =>
                    $get('brand_id')
                        ? VehicleModel::where('brand_id', $get('brand_id'))->orderBy('name')->pluck('name', 'id')
                        : []
                )
                ->searchable()
                ->required(),

            // Year
            Select::make('year_id')
                ->label('Year')
                ->options(Year::orderBy('year', 'desc')->pluck('year', 'id'))
                ->searchable()
                ->required(),

            // License Plate
            TextInput::make('license_plate')
                ->label('Plat Nomor')
                ->maxLength(20)
                ->required(),

            // Odometer
            TextInput::make('odometer')
                ->label('Odometer (KM)')
                ->numeric()
                ->minValue(0),

            // Status (default hold)
            Select::make('status')
                ->label('Status')
                ->options([
                    'hold'      => 'Hold',
                    'available' => 'Available',
                    'in_repair' => 'In Repair',
                    'sold'      => 'Sold',
                ])
                ->default('hold')
                ->required(),

            // Type (default sell)
            Select::make('type')
                ->label('Type')
                ->options([
                    'sell' => 'Sell',
                    'buy'  => 'Buy',
                ])
                ->default('sell')
                ->required(),

            // Notes
            Textarea::make('notes')
                ->label('Notes')
                ->columnSpanFull(),
        ]);
    }
}
