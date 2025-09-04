<?php

namespace App\Filament\Resources\OtherAssets\Pages;

use App\Filament\Resources\OtherAssets\OtherAssetResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditOtherAsset extends EditRecord
{
    protected static string $resource = OtherAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
             DeleteAction::make()
                ->visible(fn () => Filament::auth()->user()?->role === 'owner'),
        ];
    }
}
