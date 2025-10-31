<?php

namespace App\Filament\Resources\OtherAssets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OtherAssetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('tables.name')),

                TextEntry::make('value')
                    ->numeric()
                    ->label(__('tables.value')),

                TextEntry::make('acquisition_date')
                    ->date()
                    ->label(__('tables.acquisition_date')),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->label(__('tables.created_at')),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->label(__('tables.updated_at')),
            ]);
    }
}
