<?php

namespace App\Filament\Resources\HeaderSettings\Pages;

use App\Filament\Resources\HeaderSettings\HeaderSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHeaderSetting extends EditRecord
{
    protected static string $resource = HeaderSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
