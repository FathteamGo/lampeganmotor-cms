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
                TextEntry::make('vehicleModel.name')->label('Model'),
                TextEntry::make('type.name')->label('Type'),
                TextEntry::make('color.name')->label('Color'),
                TextEntry::make('year.year')->label('Year'),
                TextEntry::make('vin')->label('VIN'),
                TextEntry::make('engine_number')->label('Engine Number'),
                TextEntry::make('license_plate')->label('License Plate'),
                TextEntry::make('bpkb_number')->label('BPKB Number'),
                TextEntry::make('purchase_price')
                    ->label('Purchase Price')
                    ->money('IDR'),
                TextEntry::make('sale_price')
                    ->label('Sale Price')
                    ->money('IDR'),
                TextEntry::make('dp_percentage')
                    ->label('DP Percentage')
                    ->formatStateUsing(fn($state) => $state ? "{$state}%" : '-'),
                TextEntry::make('odometer')
                    ->label('Odometer (KM)')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state) . ' km' : '-'),
                TextEntry::make('status')
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'sold' => 'danger',
                        'in_repair' => 'warning',
                        'hold' => 'gray',
                        default => 'gray',
                    }),
                TextEntry::make('engine_specification')
                    ->label('Engine Specification')
                    ->html()
                    ->columnSpanFull(),
                TextEntry::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
                RepeatableEntry::make('photos')
                    ->label('Photos')
                    ->columnSpanFull()
                    ->grid(3)
                    ->schema([
                        ImageEntry::make('path')
                            ->label('')
                            ->disk('public')
                            ->height(150)
                            ->extraImgAttributes(['loading' => 'lazy']),
                        TextEntry::make('caption')->label(''),
                    ]),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ]);
    }
}
