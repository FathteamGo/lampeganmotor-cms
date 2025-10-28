<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Infolists\Components\BadgeEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class VehicleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('vehicleModel.name')
                ->label(__('tables.model'))
                ->getStateUsing(fn($state, $record) => $record->vehicleModel?->name ?? '-'),

            TextEntry::make('type.name')
                ->label(__('tables.type'))
                ->getStateUsing(fn($state, $record) => $record->type?->name ?? '-'),

            TextEntry::make('color.name')
                ->label(__('tables.color'))
                ->getStateUsing(fn($state, $record) => $record->color?->name ?? '-'),

            TextEntry::make('year.year')
                ->label(__('tables.year'))
                ->getStateUsing(fn($state, $record) => $record->year?->year ?? '-'),

            TextEntry::make('vin')->label(__('tables.vin'))
                ->getStateUsing(fn($state, $record) => $record->vin ?? '-'),

            TextEntry::make('engine_number')->label(__('tables.engine_number'))
                ->getStateUsing(fn($state, $record) => $record->engine_number ?? '-'),

            TextEntry::make('license_plate')->label(__('tables.license_plate'))
                ->getStateUsing(fn($state, $record) => $record->license_plate ?? '-'),

            TextEntry::make('bpkb_number')->label(__('tables.bpkb_number'))
                ->getStateUsing(fn($state, $record) => $record->bpkb_number ?? '-'),

            TextEntry::make('purchase_price')
                ->label(__('tables.purchase_price'))
                ->money('IDR')
                ->getStateUsing(fn($state, $record) => $record->purchase_price ?? 0),

            TextEntry::make('sale_price')
                ->label(__('tables.sale_price'))
                ->money('IDR')
                ->getStateUsing(fn($state, $record) => $record->sale_price ?? 0),

            TextEntry::make('dp_percentage')
                ->label(__('tables.dp_percentage'))
                ->formatStateUsing(fn($state) => $state ? "{$state}%" : '-'),

            TextEntry::make('odometer')
                ->label(__('tables.odometer'))
                ->formatStateUsing(fn($state) => $state ? number_format($state) . ' km' : '-'),

            TextEntry::make('status')
                ->label(__('tables.status'))
                ->color(fn(string $state): string => match ($state) {
                    'available' => 'success',
                    'sold' => 'danger',
                    'in_repair' => 'warning',
                    'hold' => 'gray',
                    default => 'gray',
                })
                ->getStateUsing(fn($state, $record) => $record->status ?? 'available'),

            TextEntry::make('engine_specification')
                ->label(__('tables.engine_specification'))
                ->html()
                ->columnSpanFull()
                ->getStateUsing(fn($state, $record) => $record->engine_specification ?? '-'),

            TextEntry::make('description')
                ->label(__('tables.description'))
                ->columnSpanFull()
                ->getStateUsing(fn($state, $record) => $record->description ?? '-'),

            // RepeatableEntry::make('photos')
            //     ->label(__('tables.photos'))
            //     ->columnSpanFull()
            //     ->grid(3)
            //     ->schema([
            //         ImageEntry::make('path')
            //             ->label('')
            //             ->height(150)
            //             ->extraImgAttributes([
            //                 'loading' => 'lazy',
            //                 'style' => 'object-fit: cover; border-radius: 10px;',
            //                 'onerror' => "this.onerror=null;this.src='" . asset('Images/logo/lampegan.png') . "';",
            //             ])
            //             ->getStateUsing(function ($state, $record) {
            //                 if (!$state) {
            //                     return asset('Images/logo/lampegan.png');
            //                 }

            //                 $url = Storage::disk('public')->url($state);
            //                 return $url;
            //             }),

            //         TextEntry::make('caption')
            //             ->label(__('tables.caption'))
            //             ->getStateUsing(fn($state) => $state ?? '-'),
            //     ]),

            TextEntry::make('created_at')
                ->label(__('tables.created_at'))
                ->dateTime()
                ->getStateUsing(fn($state, $record) => $record->created_at?->format('Y-m-d H:i:s') ?? '-'),

            TextEntry::make('updated_at')
                ->label(__('tables.updated_at'))
                ->dateTime()
                ->getStateUsing(fn($state, $record) => $record->updated_at?->format('Y-m-d H:i:s') ?? '-'),
        ]);
    }
}
