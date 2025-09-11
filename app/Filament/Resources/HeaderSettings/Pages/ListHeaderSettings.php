<?php

namespace App\Filament\Resources\HeaderSettings\Pages;

use App\Filament\Resources\HeaderSettings\HeaderSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHeaderSettings extends ListRecords
{
    protected static string $resource = HeaderSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
