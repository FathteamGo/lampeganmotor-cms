<?php

namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
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
                    ->label('Harga Motor')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float)$state, 0, ',', '.')),

                // Breakdown biaya tambahan
                RepeatableEntry::make('additionalCosts')
                    ->label('Biaya Tambahan')
                    ->schema([
                        TextEntry::make('category.name')
                            ->label('Kategori'),
                        TextEntry::make('price')
                            ->label('Harga')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float)$state, 0, ',', '.')),
                    ]),

                // Grand total
                TextEntry::make('grand_total')
                    ->label('Total Deal')
                    ->state(function ($record) {
                        $motor = (float) $record->total_price ?? 0;
                        $additional = $record->additionalCosts->sum('price') ?? 0;
                        return $motor + $additional;
                    })
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float)$state, 0, ',', '.'))
                    ->extraAttributes(['class' => 'text-xl font-bold text-green-600']),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->label('Dibuat'),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->label('Diperbarui'),
            ]);
    }
}
