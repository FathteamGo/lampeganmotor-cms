<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible(fn () => Filament::auth()->user()?->role === 'owner'),
        ];
    }

   protected function mutateFormDataBeforeFill(array $data): array
{
    // Jangan preg_replace lagi, hanya format untuk tampil di form
    if (isset($data['amount'])) {
        $data['amount'] = number_format((float) $data['amount'], 0, ',', '.');
    }
    return $data;
}

}