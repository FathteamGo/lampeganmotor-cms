<?php

namespace App\Filament\Resources\OtherAssets\Pages;

use App\Filament\Resources\OtherAssets\OtherAssetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOtherAssets extends ListRecords
{
    protected static string $resource = OtherAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
