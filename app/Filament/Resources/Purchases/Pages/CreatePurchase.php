<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use App\Models\Type;
use App\Models\Color;
use App\Models\Year;
use App\Models\VehiclePhoto;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Validasi Duplikasi VIN
        if (Vehicle::where('vin', $data['vin'])->exists()) {
            throw ValidationException::withMessages([
                'vin' => 'Nomor rangka (VIN) ini sudah terdaftar di data kendaraan!',
            ]);
        }

        // Validasi Duplikasi Nomor Mesin
        if (Vehicle::where('engine_number', $data['engine_number'])->exists()) {
            throw ValidationException::withMessages([
                'engine_number' => 'Nomor mesin ini sudah terdaftar di data kendaraan!',
            ]);
        }

        // Buat entri baru untuk model, tipe, warna, tahun (manual input)
        $model = VehicleModel::firstOrCreate(['name' => $data['vehicle_model_name']]);
        $type = Type::firstOrCreate(['name' => $data['type_name']]);
        $color = Color::firstOrCreate(['name' => $data['color_name']]);
        $year = Year::firstOrCreate(['year' => $data['year_name']]);

        // Simpan kendaraan baru
        $vehicle = Vehicle::create([
            'vehicle_model_id' => $model->id,
            'type_id' => $type->id,
            'color_id' => $color->id,
            'year_id' => $year->id,
            'vin' => $data['vin'],
            'engine_number' => $data['engine_number'],
            'license_plate' => $data['license_plate'] ?? null,
            'bpkb_number' => $data['bpkb_number'] ?? null,
            'purchase_price' => $data['purchase_price'],
            'sale_price' => $data['sale_price'] ?? 0,
            'down_payment' => $data['down_payment'] ?? 0,
            'odometer' => $data['odometer'] ?? 0,
            'engine_specification' => $data['engine_specification'] ?? null,
            'notes' => $data['vehicle_notes'] ?? null,
            'location' => $data['location'] ?? null,
            'status' => 'available',
        ]);

        // Simpan foto kendaraan (kalau ada)
        if (!empty($data['photos'])) {
            foreach ($data['photos'] as $photo) {
                if (!empty($photo['file'])) {
                    VehiclePhoto::create([
                        'vehicle_id' => $vehicle->id,
                        'path' => $photo['file'],
                        'caption' => $photo['caption'] ?? null,
                    ]);
                }
            }
        }

        // Hitung total (harga beli + biaya tambahan)
        $harga = floatval($data['purchase_price'] ?? 0);
        $tambahan = collect($data['additional_costs'] ?? [])
            ->sum(fn($item) => floatval($item['price'] ?? 0));

        $data['total_price'] = $harga + $tambahan;
        $data['vehicle_id'] = $vehicle->id;

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Data Pembelian Berhasil Disimpan!')
            ->body('Kendaraan baru berhasil ditambahkan ke daftar.')
            ->success();
    }
}
