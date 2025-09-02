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
            Section::make('Data Contact')
                ->columns(2)
                ->schema([
                    TextEntry::make('supplier.name')->label('Name'),
                TextEntry::make('supplier.phone')->label('Phone'),
                ]),

            Section::make('Detail Vehicle')
                ->columns(3)
                ->schema([
                    TextEntry::make('brand.name')->label('Merk'),
                    TextEntry::make('vehicleModel.name')->label('Model'),
                    TextEntry::make('year.year')->label('Year'),
                    TextEntry::make('odometer')->label('Odometer (KM)'),
                    TextEntry::make('license_plate')->label('Plate'),
                    TextEntry::make('status')->label('Status')->badge(),
                ]),

            Section::make('Notes')
                ->schema([
                    TextEntry::make('notes')->label('Notes'),
                ]),

            Section::make('Photos')
                ->columns(5)
                ->schema([
                    ImageEntry::make('photos.path')
                        ->disk('public')
                        ->label(''),
                ]),
        ]);
    }
}
