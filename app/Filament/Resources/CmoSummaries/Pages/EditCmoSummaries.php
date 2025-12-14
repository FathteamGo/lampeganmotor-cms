<?php

namespace App\Filament\Resources\CmoSummaries\Pages;

use App\Filament\Resources\CmoSummaries\CmoSummariesResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCmoSummaries extends EditRecord
{
    protected static string $resource = CmoSummariesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
