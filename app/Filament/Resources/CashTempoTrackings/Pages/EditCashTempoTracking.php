<?php

namespace App\Filament\Resources\CashTempoTrackings\Pages;

use App\Filament\Resources\CashTempoTrackings\CashTempoTrackingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCashTempoTracking extends EditRecord
{
    protected static string $resource = CashTempoTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
