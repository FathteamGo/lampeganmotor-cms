<?php

namespace App\Filament\Resources\SalesSummaries\Pages;

use App\Filament\Resources\SalesSummaries\SalesSummaryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalesSummaries extends ListRecords
{
    protected static string $resource = SalesSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
