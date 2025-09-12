<?php

namespace App\Filament\Resources\VehicleModels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\Brand;
use Illuminate\Validation\Rule;

class VehicleModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                Select::make('brand_id')
                    ->label('Merek')
                    ->options(Brand::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                TextInput::make('name')
                    ->label('Nama Model')
                    ->required()
                    ->maxLength(255)
                    ->rule(fn ($record) => Rule::unique('vehicle_models', 'name')->ignore($record))
                    ->validationMessages([
                        'unique' => 'Nama model kendaraan sudah terdaftar.',
                    ]),
            ]);
    }
}
