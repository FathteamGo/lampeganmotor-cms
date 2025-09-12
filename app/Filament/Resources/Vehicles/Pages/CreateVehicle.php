<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class CreateVehicle extends CreateRecord
{
    protected static string $resource = VehicleResource::class;

    /**
     * Tangani proses create agar bisa munculin notif kalau ada duplikat
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (ValidationException $e) {
            // ambil semua pesan error
            $messages = collect($e->errors())->flatten();

            // kalau ada error unique → tampilkan toast
            if ($messages->some(fn($msg) => str_contains(strtolower($msg), 'unique'))) {
                Notification::make()
                    ->danger()
                    ->title('Data Duplikat')
                    ->body('Data kendaraan dengan field unik (VIN, No Mesin, No Plat, atau BPKB) sudah terdaftar.')
                    ->send();
            }

            throw $e; // biar field tetap ditandai merah juga
        }
    }
}
