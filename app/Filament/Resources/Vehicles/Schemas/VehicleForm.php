<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
            ->columns(2)
            ->schema([
                // Vehicle Model Dropdown
                Select::make('vehicle_model_id')
                    ->label(__('navigation.vehicle_models'))
                    ->options(VehicleModel::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                // Type Dropdown
                Select::make('type_id')
                    ->label(__('tables.type'))
                    ->options(Type::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                // Color Dropdown
                Select::make('color_id')
                    ->label(__('tables.color'))
                    ->options(Color::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                // Year Dropdown
                Select::make('year_id')
                    ->label(__('tables.year'))
                    ->options(Year::orderBy('year')->pluck('year', 'id'))
                    ->searchable()
                    ->required(),

                // VIN
                TextInput::make('vin')
                    ->label(__('tables.vin'))
                    ->required()
                    ->maxLength(255),

                // Engine Number
                TextInput::make('engine_number')
                    ->label(__('tables.engine_number'))
                    ->required()
                    ->maxLength(255),

                // License Plate
                TextInput::make('license_plate')
                    ->label(__('tables.license_plate'))
                    ->maxLength(255),

                // BPKB Number
                TextInput::make('bpkb_number')
                    ->label(__('tables.bpkb_number'))
                    ->maxLength(255),

                // Purchase Price
                TextInput::make('purchase_price')
                    ->label(__('tables.purchase_price'))
                    ->required()
                    ->numeric(),

                // Sale Price
                TextInput::make('sale_price')
                    ->label(__('tables.sale_price'))
                    ->numeric(),

                // DP Percentage
                TextInput::make('dp_percentage')
                    ->label(__('tables.dp_percentage'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),

                // Odometer
                TextInput::make('odometer')
                    ->label(__('tables.odometer'))
                    ->numeric()
                    ->minValue(0),

                // Status Dropdown
                Select::make('status')
                    ->label(__('tables.status'))
                    ->options([
                        'available' => __('tables.available'),
                        'sold' => __('tables.sold'),
                        'in_repair' => __('tables.in_repair'),
                        'hold' => __('tables.hold'),
                    ])
                    ->default('hold')
                    ->required(),

                // Engine Specification
                TextInput::make('engine_specification')
                    ->label(__('tables.engine_specification')),

                // Location
                TextInput::make('location')
                    ->label(__('tables.location')),

                // Notes
                RichEditor::make('notes')
                    ->label(__('tables.notes'))
                    ->columnSpanFull(),

                // Description
                RichEditor::make('description')
                    ->label(__('tables.description'))
                    ->columnSpanFull(),

                // Photos Repeater
                Repeater::make('photos')
                    ->relationship()
                    ->label(__('tables.photos'))
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('path')
                            ->label(__('tables.image'))
                            ->image()
                            ->disk('public')
                            ->directory('vehicle-photos')
                            ->required(),
                        TextInput::make('caption')
                            ->label(__('tables.caption')),
                    ])->grid(2),
            ]);
    }
}
