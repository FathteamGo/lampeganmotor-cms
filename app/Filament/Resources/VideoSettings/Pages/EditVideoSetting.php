<?php

namespace App\Filament\Resources\VideoSettings\Pages;

use App\Filament\Resources\VideoSettings\VideoSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVideoSetting extends EditRecord
{
    protected static string $resource = VideoSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
