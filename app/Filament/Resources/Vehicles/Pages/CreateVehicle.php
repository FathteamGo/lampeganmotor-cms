<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

use App\Models\Brand;
use App\Models\VehicleModel;
use App\Models\Type;
use App\Models\Color;
use App\Models\Year;

class CreateVehicle extends CreateRecord
{
    protected static string $resource = VehicleResource::class;

    /**
     * 🔹 Sebelum data kendaraan dibuat, buat relasi entitasnya dulu
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Buat entri brand
        $brand = Brand::firstOrCreate(['name' => $data['brand_name']]);

        // Buat model kendaraan, kaitkan ke brand
        $model = VehicleModel::firstOrCreate([
            'name' => $data['vehicle_model_name'],
            'brand_id' => $brand->id,
        ]);

        // Buat entri tipe, warna, dan tahun
        $type = Type::firstOrCreate(['name' => $data['type_name']]);
        $color = Color::firstOrCreate(['name' => $data['color_name']]);
        $year = Year::firstOrCreate(['year' => $data['year_name']]);

        // Isi relasi ID ke tabel vehicles
        $data['vehicle_model_id'] = $model->id;
        $data['type_id'] = $type->id;
        $data['color_id'] = $color->id;
        $data['year_id'] = $year->id;

        return $data;
    }

    /**
     * 🔹 Tangani duplikasi (VIN, mesin, BPKB, plat)
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordCreation($data);
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
