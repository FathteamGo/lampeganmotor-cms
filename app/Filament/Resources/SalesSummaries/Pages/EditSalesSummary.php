<?php

namespace App\Filament\Resources\SalesSummaries\Pages;

use App\Filament\Resources\SalesSummaries\SalesSummaryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSalesSummary extends EditRecord
{
    protected static string $resource = SalesSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }
}
