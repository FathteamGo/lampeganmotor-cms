<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VehicleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('vehicle_model_id')
                    ->numeric(),
                TextEntry::make('type_id')
                    ->numeric(),
                TextEntry::make('color_id')
                    ->numeric(),
                TextEntry::make('year_id')
                    ->numeric(),
                TextEntry::make('vin'),
                TextEntry::make('engine_number'),
                TextEntry::make('license_plate'),
                TextEntry::make('bpkb_number'),
                TextEntry::make('purchase_price')
                    ->numeric(),
                TextEntry::make('sale_price')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
