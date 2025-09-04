<?php

namespace App\Filament\Resources\OtherAssets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OtherAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('tables.name'))
                    ->required(),

                Textarea::make('description')
                    ->label(__('tables.description'))
                    ->columnSpanFull(),

                TextInput::make('value')
                    ->label(__('tables.value'))
                    ->required()
                    ->numeric(),

                DatePicker::make('acquisition_date')
                    ->label(__('tables.acquisition_date'))
                    ->required(),
            ]);
    }
}
