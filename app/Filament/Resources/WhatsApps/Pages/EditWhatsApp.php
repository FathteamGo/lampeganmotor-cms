<?php

namespace App\Filament\Resources\WhatsApps\Pages;

use App\Filament\Resources\WhatsApps\WhatsAppResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWhatsApp extends EditRecord
{
    protected static string $resource = WhatsAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
