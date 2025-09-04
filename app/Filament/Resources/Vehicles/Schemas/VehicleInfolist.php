<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Infolists\Components\BadgeEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VehicleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('vehicleModel.name')->label(__('tables.model')),
                TextEntry::make('type.name')->label(__('tables.type')),
                TextEntry::make('color.name')->label(__('tables.color')),
                TextEntry::make('year.year')->label(__('tables.year')),
                TextEntry::make('vin')->label(__('tables.vin')),
                TextEntry::make('engine_number')->label(__('tables.engine_number')),
                TextEntry::make('license_plate')->label(__('tables.license_plate')),
                TextEntry::make('bpkb_number')->label(__('tables.bpkb_number')),
                TextEntry::make('purchase_price')
                    ->label(__('tables.purchase_price'))
                    ->money('IDR'),
                TextEntry::make('sale_price')
                    ->label(__('tables.sale_price'))
                    ->money('IDR'),
                TextEntry::make('dp_percentage')
                    ->label(__('tables.dp_percentage'))
                    ->formatStateUsing(fn($state) => $state ? "{$state}%" : '-'),
                TextEntry::make('odometer')
                    ->label(__('tables.odometer'))
                    ->formatStateUsing(fn ($state) => $state ? number_format($state) . ' km' : '-'),
                TextEntry::make('status')
                    ->label(__('tables.status'))
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
                    ->columnSpanFull(),
                TextEntry::make('description')
                    ->label(__('tables.description'))
                    ->columnSpanFull(),
                RepeatableEntry::make('photos')
                    ->label(__('tables.photos'))
                    ->columnSpanFull()
                    ->grid(3)
                    ->schema([
                        ImageEntry::make('path')
                            ->label('')
                            ->disk('public')
                            ->height(150)
                            ->extraImgAttributes(['loading' => 'lazy']),
                        TextEntry::make('caption')->label(__('tables.caption')),
                    ]),
                TextEntry::make('created_at')
                    ->label(__('tables.created_at'))
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label(__('tables.updated_at'))
                    ->dateTime(),
            ]);
    }
}
