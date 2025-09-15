<?php

namespace App\Filament\Resources\StnkRenewals\Pages;

use App\Filament\Resources\StnkRenewals\StnkRenewalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStnkRenewals extends ListRecords
{
    protected static string $resource = StnkRenewalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
