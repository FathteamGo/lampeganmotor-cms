<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\VehicleModel;
use App\Models\Type;
use App\Models\Color;
use App\Models\Year;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Vehicle Model Dropdown
                Select::make('vehicle_model_id')
                    ->label('Vehicle Model')
                    ->options(VehicleModel::all()->pluck('name', 'id')) // Mengambil data dari model VehicleModel
                    ->searchable() // Menambahkan fitur pencarian
                    ->required(),

                // Type Dropdown
                Select::make('type_id')
                    ->label('Type')
                    ->options(Type::all()->pluck('name', 'id')) // Mengambil data dari model Type
                    ->searchable()
                    ->required(),

                // Color Dropdown
                Select::make('color_id')
                    ->label('Color')
                    ->options(Color::all()->pluck('name', 'id')) // Mengambil data dari model Color
                    ->searchable()
                    ->required(),

                // Year Dropdown
                Select::make('year_id')
                    ->label('Year')
                    ->options(Year::all()->pluck('year', 'id')) // Mengambil data dari model Year
                    ->searchable()
                    ->required(),

                // VIN
                TextInput::make('vin')
                    ->label('VIN')
                    ->required(),

                // Engine Number
                TextInput::make('engine_number')
                    ->label('Engine Number')
                    ->required(),

                // License Plate
                TextInput::make('license_plate')
                    ->label('License Plate'),

                // BPKB Number
                TextInput::make('bpkb_number')
                    ->label('BPKB Number'),

                // Purchase Price
                TextInput::make('purchase_price')
                    ->label('Purchase Price')
                    ->required()
                    ->numeric(),

                // Sale Price
                TextInput::make('sale_price')
                    ->label('Sale Price')
                    ->numeric(),

                // DP Percentage
                TextInput::make('dp_percentage')
                    ->label('DP Percentage')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
                // Status Dropdown
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Available',
                        'sold' => 'Sold',
                        'in_repair' => 'In Repair',
                        'hold' => 'Hold',
                    ])
                    ->default('hold')
                    ->required(),

                // Engine Specification
                TextInput::make('engine_specification')
                    ->label('Engine Specification'),
                // Location
                TextInput::make('location')
                    ->label('Location'),

                // Notes
                RichEditor::make('notes')
                    ->label('Notes')
                    ->columnSpanFull(),

                // Description
                RichEditor::make('description')
                    ->label('Description')
                    ->columnSpanFull(),

                // Photos Repeater
                Repeater::make('photos')
                    ->relationship()
                    ->label('Photos')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('path')->label('Image')->image()->disk('public')->directory('vehicle-photos')->required(),
                        TextInput::make('caption')->label('Caption'),
                    ])->grid(2),
            ]);
    }
}
