<?php

namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PurchaseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('vehicle.vehicleModel.name')
                    ->label('Model'),

                TextEntry::make('supplier.name')
                    ->label('Supplier'),

                TextEntry::make('purchase_date')
                    ->date()
                    ->label('Tanggal Pembelian'),

                TextEntry::make('total_price')
                    ->numeric()
                    ->prefix('Rp')
                    ->label('Total Harga'),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->label('Dibuat'),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->label('Diperbarui'),
            ]);
    }
}
