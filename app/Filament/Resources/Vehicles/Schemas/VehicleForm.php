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
use Illuminate\Validation\Rule;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                // Vehicle Model Dropdown
                Select::make('vehicle_model_id')
                    ->label('Model Kendaraan')
                    ->options(VehicleModel::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                // Type Dropdown
                Select::make('type_id')
                    ->label('Tipe')
                    ->options(Type::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                // Color Dropdown
                Select::make('color_id')
                    ->label('Warna')
                    ->options(Color::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                // Year Dropdown
                Select::make('year_id')
                    ->label('Tahun')
                    ->options(Year::orderBy('year')->pluck('year', 'id'))
                    ->searchable()
                    ->required(),

                // VIN
                TextInput::make('vin')
                    ->label('Nomor Rangka (VIN)')
                    ->required()
                    ->maxLength(255)
                    ->rule(fn ($record) => Rule::unique('vehicles', 'vin')->ignore($record))
                    ->validationMessages([
                        'unique' => 'Nomor Rangka (VIN) sudah terdaftar.',
                    ]),

                // Engine Number
                TextInput::make('engine_number')
                    ->label('Nomor Mesin')
                    ->required()
                    ->maxLength(255)
                    ->rule(fn ($record) => Rule::unique('vehicles', 'engine_number')->ignore($record))
                    ->validationMessages([
                        'unique' => 'Nomor Mesin sudah terdaftar.',
                    ]),

                // License Plate
                TextInput::make('license_plate')
                    ->label('Plat Nomor')
                    ->maxLength(255)
                    ->rule(fn ($record) => Rule::unique('vehicles', 'license_plate')->ignore($record))
                    ->validationMessages([
                        'unique' => 'Plat Nomor sudah terdaftar.',
                    ]),

                // BPKB Number
                TextInput::make('bpkb_number')
                    ->label('Nomor BPKB')
                    ->maxLength(255)
                    ->rule(fn ($record) => Rule::unique('vehicles', 'bpkb_number')->ignore($record))
                    ->validationMessages([
                        'unique' => 'Nomor BPKB sudah terdaftar.',
                    ]),

                // Purchase Price
                TextInput::make('purchase_price')
                    ->label('Harga Beli')
                    ->required()
                    ->numeric(),

                // Sale Price
                TextInput::make('sale_price')
                    ->label('Harga Jual')
                    ->numeric(),

                // DP Percentage
                TextInput::make('dp_percentage')
                    ->label('Persentase DP')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),

                // Odometer
                TextInput::make('odometer')
                    ->label('Odometer')
                    ->numeric()
                    ->minValue(0),

                // Status Dropdown
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Tersedia',
                        'sold' => 'Terjual',
                        'in_repair' => 'Perbaikan',
                        'hold' => 'Ditahan',
                    ])
                    ->default('hold')
                    ->required(),

                // Engine Specification
                TextInput::make('engine_specification')
                    ->label('Spesifikasi Mesin'),

                // Location
                TextInput::make('location')
                    ->label('Lokasi'),

                // Notes
                RichEditor::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),

                // Description
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),

                // Photos Repeater
                Repeater::make('photos')
                    ->relationship()
                    ->label('Foto Kendaraan')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('path')
                            ->label('Gambar')
                            ->image()
                            ->disk('public')
                            ->directory('vehicle-photos')
                            ->required(),
                        TextInput::make('caption')
                            ->label('Keterangan'),
                    ])->grid(2),
            ]);
    }
}
