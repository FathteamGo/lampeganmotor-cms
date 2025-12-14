<?php

namespace App\Filament\Resources\CmoSummaries\Pages;

use App\Filament\Resources\CmoSummaries\CmoSummariesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCmoSummaries extends ListRecords
{
    protected static string $resource = CmoSummariesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
