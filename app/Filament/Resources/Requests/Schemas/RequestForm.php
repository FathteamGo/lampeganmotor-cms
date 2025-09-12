<?php

namespace App\Filament\Resources\Requests\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use App\Models\Supplier;
use App\Models\Brand;
use App\Models\VehicleModel;
use App\Models\Year;
use App\Models\Vehicle;

class RequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // Supplier
            Select::make('supplier_id')
                ->label(__('tables.purchase_supplier'))
                ->options(Supplier::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),

            // Brand
            Select::make('brand_id')
                ->label(__('tables.brand'))
                ->options(Brand::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required()
                ->reactive(),

            // Vehicle Model (filtered by brand)
            Select::make('vehicle_model_id')
                ->label(__('tables.model'))
                ->options(fn ($get) =>
                    $get('brand_id')
                        ? VehicleModel::where('brand_id', $get('brand_id'))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                        : []
                )
                ->searchable()
                ->required(),

            // Year
            Select::make('year_id')
                ->label(__('tables.year'))
                ->options(Year::orderBy('year', 'desc')->pluck('year', 'id'))
                ->searchable()
                ->required(),

            // Vehicle (PILIH kendaraan yg sudah ada → no duplikat)
            Select::make('vehicle_id')
                ->label(__('tables.vehicle'))
                ->options(
                    Vehicle::query()
                        ->where('status', '!=', 'sold') // contoh: hanya kendaraan yg belum terjual
                        ->orderBy('license_plate')
                        ->pluck('license_plate', 'id')
                )
                ->searchable()
                ->required(),

            // Odometer
            Select::make('odometer')
                ->label(__('tables.odometer'))
                ->options(
                    Vehicle::pluck('odometer', 'odometer')->unique()
                )
                ->searchable(),

            // Status (default hold)
            Select::make('status')
                ->label(__('tables.status'))
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
                ->label(__('tables.type'))
                ->options([
                    'sell' => 'Sell',
                    'buy'  => 'Buy',
                ])
                ->default('sell')
                ->required(),

            // Notes
            Textarea::make('notes')
                ->label(__('tables.note'))
                ->columnSpanFull(),
        ]);
    }
}
