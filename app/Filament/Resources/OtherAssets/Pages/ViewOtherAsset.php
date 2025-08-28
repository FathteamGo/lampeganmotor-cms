<?php

namespace App\Filament\Resources\OtherAssets\Pages;

use App\Filament\Resources\OtherAssets\OtherAssetResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOtherAsset extends ViewRecord
{
    protected static string $resource = OtherAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
