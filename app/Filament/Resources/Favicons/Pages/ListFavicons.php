<?php

namespace App\Filament\Resources\Favicons\Pages;

use App\Filament\Resources\Favicons\FaviconResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFavicons extends ListRecords
{
    protected static string $resource = FaviconResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
