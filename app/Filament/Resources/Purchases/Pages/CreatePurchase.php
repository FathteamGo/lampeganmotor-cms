<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use App\Models\Type;
use App\Models\Color;
use App\Models\Year;
use App\Models\Brand;
use App\Models\VehiclePhoto;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        //buatkan logic jika category addtional coasts tidak ada make default Tidak Ada
       if (empty($data['additionalCosts'])) {
            $data['additionalCosts'] = [
                [
                    'category' => 'Tidak Ada',
                    'price' => 0,
                ],
            ];
        }

       
        // 🔹 Validasi VIN dan Engine Number
        if (Vehicle::where('vin', $data['vin'])->exists()) {
            throw ValidationException::withMessages([
                'vin' => 'Nomor rangka sudah terdaftar',
            ]);
        }

        if (Vehicle::where('engine_number', $data['engine_number'])->exists()) {
            throw ValidationException::withMessages([
                'engine_number' => 'Nomor mesin sudah terdaftar',
            ]);
        }

        // 🔹 Buat atau ambil data master
        $brand = Brand::firstOrCreate(['name' => $data['brand_name']]);
        $model = VehicleModel::firstOrCreate([
            'name' => $data['vehicle_model_name'],
            'brand_id' => $brand->id,
        ]);

        $type = Type::firstOrCreate(['name' => $data['type_name']]);
        $color = Color::firstOrCreate(['name' => $data['color_name']]);
        $year = Year::firstOrCreate(['year' => $data['year_name']]);

        // 🔹 Buat Vehicle
        $vehicle = Vehicle::create([
            'vehicle_model_id' => $model->id,
            'type_id' => $type->id,
            'color_id' => $color->id,
            'year_id' => $year->id,
            'vin' => $data['vin'],
            'engine_number' => $data['engine_number'],
            'license_plate' => $data['license_plate'] ?? null,
            'bpkb_number' => $data['bpkb_number'] ?? null,
            'purchase_price' => $data['purchase_price'], // sudah clean number
            'sale_price' => $data['sale_price'] ?? null,
            'down_payment' => $data['down_payment'] ?? null,
            'odometer' => $data['odometer'] ?? null,
            'engine_specification' => $data['engine_specification'] ?? null,
            'notes' => $data['vehicle_notes'] ?? null,
            'location' => $data['location'] ?? null,
            'status' => 'available',
        ]);

        // 🔹 Simpan Photos ke Vehicle
        if (!empty($data['photos'])) {
            foreach ($data['photos'] as $photoData) {
                if (!empty($photoData['path'])) {
                    VehiclePhoto::create([
                        'vehicle_id' => $vehicle->id,
                        'path' => $photoData['path'],
                        'caption' => $photoData['caption'] ?? null,
                    ]);
                }
            }
        }

        // 🔹 Set vehicle_id untuk Purchase
        $data['vehicle_id'] = $vehicle->id;

        // 🔹 Hitung total_price (harga beli + biaya tambahan)
        $data['total_price'] = intval($data['purchase_price']) +
            collect($data['additionalCosts'] ?? [])
                ->sum(fn ($item) => intval($item['price'] ?? 0));

        // 🔹 Hapus field yang tidak perlu disimpan ke tabel purchases
        unset($data['photos']); // sudah disimpan ke vehicle_photos
        unset($data['brand_name']);
        unset($data['vehicle_model_name']);
        unset($data['type_name']);
        unset($data['color_name']);
        unset($data['year_name']);
        unset($data['vehicle_notes']);
        unset($data['engine_specification']);
        unset($data['location']);

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