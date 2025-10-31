<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VehicleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('vehicleModel.name')
                ->label(__('tables.model'))
                ->default('-'),

            TextEntry::make('type.name')
                ->label(__('tables.type'))
                ->default('-'),

            TextEntry::make('color.name')
                ->label(__('tables.color'))
                ->default('-'),

            TextEntry::make('year.year')
                ->label(__('tables.year'))
                ->default('-'),

            TextEntry::make('vin')
                ->label(__('tables.vin'))
                ->default('-'),

            TextEntry::make('engine_number')
                ->label(__('tables.engine_number'))
                ->default('-'),

            TextEntry::make('license_plate')
                ->label(__('tables.license_plate'))
                ->default('-'),

            TextEntry::make('bpkb_number')
                ->label(__('tables.bpkb_number'))
                ->default('-'),

            TextEntry::make('purchase_price')
                ->label(__('tables.purchase_price'))
                ->money('IDR')
                ->default(0),

            TextEntry::make('sale_price')
                ->label(__('tables.sale_price'))
                ->money('IDR')
                ->default(0),

            TextEntry::make('down_payment')
                ->label(__('DP'))
                ->money('IDR')
                ->default(0),

            TextEntry::make('odometer')
                ->label(__('tables.odometer'))
                ->formatStateUsing(fn($state) => $state ? number_format($state) . ' km' : '-'),

            TextEntry::make('status')
                ->label(__('tables.status'))
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'available' => 'success',
                    'sold' => 'danger',
                    'in_repair' => 'warning',
                    'hold' => 'gray',
                    default => 'gray',
                }),

            TextEntry::make('engine_specification')
                ->label(__('tables.engine_specification'))
                ->html()
                ->columnSpanFull()
                ->default('-'),

            TextEntry::make('description')
                ->label(__('tables.description'))
                ->columnSpanFull(),

            TextEntry::make('created_at')
                ->label(__('tables.created_at'))
                ->dateTime(),

            TextEntry::make('updated_at')
                ->label(__('tables.updated_at'))
                ->dateTime(),
        ]);
    }
}