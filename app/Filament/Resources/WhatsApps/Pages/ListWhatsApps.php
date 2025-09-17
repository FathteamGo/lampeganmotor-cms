<?php

namespace App\Filament\Resources\WhatsApps\Pages;

use App\Filament\Resources\WhatsApps\WhatsAppResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWhatsApps extends ListRecords
{
    protected static string $resource = WhatsAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
