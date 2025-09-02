<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('vehicle.vehicleModel.name')
                    ->label('Model'),

                TextEntry::make('vehicle.color.name')
                    ->label('Warna'),

                TextEntry::make('vehicle.license_plate')
                    ->label('License Plate'),

                TextEntry::make('customer.name')
                    ->label('Customer'),

                TextEntry::make('sale_date')
                    ->date()
                    ->label('Sale Date'),

                TextEntry::make('sale_price')
                    ->numeric()
                    ->label('Sale Price'),

                TextEntry::make('payment_method')
                    ->label('Payment Method'),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->label('Created At'),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->label('Updated At'),
            ]);
    }
}
