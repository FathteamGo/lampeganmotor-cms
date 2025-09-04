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
            ->columns(2) // Bisa diubah ke 2 jika mau layout 2 kolom
            ->schema([
                Select::make('brand_id')
                    ->label(__('tables.brand')) // Multi-bahasa
                    ->options(Brand::orderBy('name')->pluck('name', 'id')) // Ambil data Brand
                    ->searchable()
                    ->required(),

                TextInput::make('name')
                    ->label(__('tables.name')) // Multi-bahasa
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
