<?php

namespace App\Filament\Resources\StnkRenewals\Pages;

use App\Filament\Resources\StnkRenewals\StnkRenewalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStnkRenewal extends EditRecord
{
    protected static string $resource = StnkRenewalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
