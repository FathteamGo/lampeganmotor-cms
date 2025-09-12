<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Filament\Actions;
use Filament\Facades\Filament;

class EditVehicle extends EditRecord
{
    protected static string $resource = VehicleResource::class;

    /**
     * Tambah header actions (View + Delete hanya untuk owner)
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => Filament::auth()->user()?->role === 'owner'),
        ];
    }

    /**
     * Tangani proses update agar bisa munculin notif kalau ada duplikat
     */
    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (ValidationException $e) {
            $messages = collect($e->errors())->flatten();

            if ($messages->some(fn($msg) => str_contains(strtolower($msg), 'unique'))) {
                Notification::make()
                    ->danger()
                    ->title('Data Duplikat')
                    ->body('Data kendaraan dengan field unik (VIN, No Mesin, No Plat, atau BPKB) sudah terdaftar.')
                    ->send();
            }

            throw $e;
        }
    }
}
