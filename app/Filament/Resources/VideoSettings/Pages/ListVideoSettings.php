<?php

namespace App\Filament\Resources\VideoSettings\Pages;

use App\Filament\Resources\VideoSettings\VideoSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVideoSettings extends ListRecords
{
    protected static string $resource = VideoSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
