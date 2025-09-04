<?php

namespace App\Filament\Resources\Requests\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;

class RequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('tables.contact_data'))
                ->columns(2)
                ->schema([
                    TextEntry::make('supplier.name')->label(__('tables.name')),
                    TextEntry::make('supplier.phone')->label(__('tables.phone')),
                ]),

            Section::make(__('tables.vehicle_detail'))
                ->columns(3)
                ->schema([
                    TextEntry::make('brand.name')->label(__('tables.brand')),
                    TextEntry::make('vehicleModel.name')->label(__('tables.model')),
                    TextEntry::make('year.year')->label(__('tables.year')),
                    TextEntry::make('odometer')->label(__('tables.odometer')),
                    TextEntry::make('license_plate')->label(__('tables.license_plate')),
                    TextEntry::make('status')->label(__('tables.status'))->badge(),
                ]),

            Section::make(__('tables.notes'))
                ->schema([
                    TextEntry::make('notes')->label(__('tables.notes')),
                ]),

            Section::make(__('tables.photos'))
                ->columns(5)
                ->schema([
                    ImageEntry::make('photos.path')
                        ->disk('public')
                        ->label(''),
                ]),
        ]);
    }
}
