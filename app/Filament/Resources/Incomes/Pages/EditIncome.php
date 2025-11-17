<?php

namespace App\Filament\Resources\Incomes\Pages;

use App\Filament\Resources\Incomes\IncomeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditIncome extends EditRecord
{
    protected static string $resource = IncomeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible(fn () => Filament::auth()->user()?->role === 'owner'),
        ];
    }

    // Format angka sebelum ditampilkan di form
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['amount'])) {
            // Hanya untuk tampil, tetap simpan angka asli di DB
            $data['amount'] = number_format((float) $data['amount'], 0, ',', '.');
        }
        return $data;
    }
}
