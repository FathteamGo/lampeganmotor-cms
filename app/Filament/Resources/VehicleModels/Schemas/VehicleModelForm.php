<?php

namespace App\Filament\Resources\VehicleModels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\Brand;

class VehicleModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('brand_id')
                    ->label('Brand') // Label untuk dropdown
                    ->options(Brand::all()->pluck('name', 'id')) // Mengambil data dari model Brand
                    ->searchable() // Menambahkan fitur pencarian
                    ->required(), // Menjadikan field ini wajib diisi
                TextInput::make('name')
                    ->required(),
            ]);
    }
}
