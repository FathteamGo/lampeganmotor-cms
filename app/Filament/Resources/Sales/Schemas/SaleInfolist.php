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
                    ->label(__('tables.model')),

                TextEntry::make('vehicle.color.name')
                    ->label(__('tables.color')),

                TextEntry::make('vehicle.license_plate')
                    ->label(__('tables.license_plate')),

                TextEntry::make('customer.name')
                    ->label(__('tables.customer')),

                TextEntry::make('sale_date')
                    ->date()
                    ->label(__('tables.sale_date')),

                TextEntry::make('sale_price')
                    ->numeric()
                    ->label(__('tables.sale_price')),

                TextEntry::make('payment_method')
                    ->label(__('tables.payment_method')),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->label(__('tables.created_at')),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->label(__('tables.updated_at')),
            ]);
    }
}
