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
use Illuminate\Support\Facades\Log; //  TAMBAHKAN INI

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        //  LOG 1: Data mentah dari form
        Log::info('=== DATA MENTAH DARI FORM ===', [
            'all_keys' => array_keys($data),
            'vehicle_photos_exists' => isset($data['vehicle_photos']),
            'vehicle_photos_count' => isset($data['vehicle_photos']) ? count($data['vehicle_photos']) : 0,
            'full_data' => $data,
        ]);

        // Buatkan logic jika category additional costs tidak ada make default Tidak Ada
        if (empty($data['additionalCosts'])) {
            $data['additionalCosts'] = [
                [
                    'category' => 'Tidak Ada',
                    'price' => 0,
                ],
            ];
        }

        //  LOG 2: Sebelum validasi
        Log::info('=== SEBELUM VALIDASI VIN/ENGINE ===');

        try {
            $exist = false;
            // Validasi VIN dan Engine Number
            if (Vehicle::where('vin', $data['vin'])->exists()) {
                $exist = true;
                // Log::warning('VIN sudah terdaftar', ['vin' => $data['vin']]);
                // throw ValidationException::withMessages([
                //     'vin' => 'Nomor rangka sudah terdaftar',
                // ]);
            }

            if (Vehicle::where('engine_number', $data['engine_number'])->exists()) {
                 $exist = true;
                // Log::warning('Engine number sudah terdaftar', ['engine_number' => $data['engine_number']]);
                // throw ValidationException::withMessages([
                //     'engine_number' => 'Nomor mesin sudah terdaftar',
                // ]);
            }

            //  LOG 3: Sebelum create master data
            Log::info('=== MULAI CREATE MASTER DATA ===');

            // Buat atau ambil data master
            $brand = Brand::firstOrCreate(['name' => $data['brand_name']]);
            Log::info('Brand created/found', ['id' => $brand->id, 'name' => $brand->name]);

            $model = VehicleModel::firstOrCreate([
                'name' => $data['vehicle_model_name'],
                'brand_id' => $brand->id,
            ]);
            Log::info('Model created/found', ['id' => $model->id, 'name' => $model->name]);

            $type = Type::firstOrCreate(['name' => $data['type_name']]);
            $color = Color::firstOrCreate(['name' => $data['color_name']]);
            $year = Year::firstOrCreate(['year' => $data['year_name']]);

            //  LOG 4: Sebelum create vehicle
            Log::info('=== MULAI CREATE VEHICLE ===', [
                'vehicle_data' => [
                    'vehicle_model_id' => $model->id,
                    'type_id' => $type->id,
                    'color_id' => $color->id,
                    'year_id' => $year->id,
                    'vin' => $data['vin'],
                    'engine_number' => $data['engine_number'],
                ]
            ]);

            if($exist == false) {
            // Buat Vehicle
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
                'sale_price' => $data['sale_price'] ?? null,
                'down_payment' => $data['down_payment'] ?? null,
                'odometer' => $data['odometer'] ?? null,
                'engine_specification' => $data['engine_specification'] ?? null,
                'notes' => $data['vehicle_notes'] ?? null,
                'location' => $data['location'] ?? null,
                'status' => 'available',
                ]);
            } else {
                // Jika ada duplikat, ambil data kendaraan yang sudah ada
                $vehicle = Vehicle::where('vin', $data['vin'])
                            // ->orWhere('engine_number', $data['engine_number']) // Lebih aman based on VIN saja untuk menghindari ambiguitas, atau tetap biarkan dual check jika business logic memerlukannya. Mari kita pertahankan logika asli tapi tambahkan update.
                            ->orWhere('engine_number', $data['engine_number'])
                            ->first();

                // UPDATE DATA KENDARAAN EXISTING (BUYBACK / RESTOCK)
                // Ini penting agar status 'sold' berubah kembali menjadi 'available'
                // dan data-data baru (harga beli baru, km baru, dll) terupdate.
                $vehicle->update([
                    'status' => 'available', // KUNCI UTAMA PERBAIKAN: Aktifkan kembali stok
                    
                    // Update spesifikasi fisik (jika ada perubahan/koreksi input)
                    'vehicle_model_id' => $model->id,
                    'type_id' => $type->id,
                    'color_id' => $color->id,
                    'year_id' => $year->id,
                    
                    // Update data transaksional & kondisi
                    'purchase_price' => $data['purchase_price'], // Update harga beli terbaru
                    'sale_price' => $data['sale_price'] ?? null, // Reset/Update harga jual
                    'down_payment' => $data['down_payment'] ?? null,
                    
                    // Update identitas legal & kondisi fisik
                    'license_plate' => $data['license_plate'] ?? $vehicle->license_plate,
                    'bpkb_number' => $data['bpkb_number'] ?? $vehicle->bpkb_number,
                    'odometer' => $data['odometer'] ?? $vehicle->odometer,
                    'engine_specification' => $data['engine_specification'] ?? $vehicle->engine_specification,
                    'location' => $data['location'] ?? $vehicle->location,
                    'notes' => $data['vehicle_notes'] ?? $vehicle->notes,
                ]);
                
                Log::info('Vehicle updated for buyback', ['vehicle_id' => $vehicle->id, 'old_status' => 'sold', 'new_status' => 'available']);
            }

            Log::info('Vehicle created successfully', ['vehicle_id' => $vehicle->id]);

            //  LOG 5: Sebelum simpan foto
            Log::info('=== MULAI SIMPAN FOTO ===', [
                'has_vehicle_photos' => !empty($data['vehicle_photos']),
                'photo_count' => !empty($data['vehicle_photos']) ? count($data['vehicle_photos']) : 0,
                'photos_data' => $data['vehicle_photos'] ?? null,
            ]);

            if (!empty($data['vehicle_photos'])) {
                foreach ($data['vehicle_photos'] as $index => $photoData) {
                    Log::info("Processing photo #{$index}", [
                        'has_path' => !empty($photoData['path']),
                        'path' => $photoData['path'] ?? null,
                        'caption' => $photoData['caption'] ?? null,
                    ]);

                    if (!empty($photoData['path'])) {
                        $photo = VehiclePhoto::create([
                            'vehicle_id' => $vehicle->id,
                            'path' => $photoData['path'],
                            'caption' => $photoData['caption'] ?? null,
                        ]);
                        Log::info("Photo #{$index} saved", ['photo_id' => $photo->id]);
                    }
                }
            }

            // Set vehicle_id untuk Purchase
            $data['vehicle_id'] = $vehicle->id;

            //  LOG 6: Hitung total
            $additionalTotal = collect($data['additionalCosts'] ?? [])
                ->sum(fn ($item) => intval($item['price'] ?? 0));

            Log::info('=== CALCULATE TOTAL ===', [
                'purchase_price' => $data['purchase_price'],
                'additional_total' => $additionalTotal,
            ]);

            // Hitung total_price (harga beli + biaya tambahan)
            $data['total_price'] = intval($data['purchase_price']) + $additionalTotal;

            //  LOG 7: Sebelum unset fields
            Log::info('=== SEBELUM UNSET FIELDS ===', [
                'all_keys_before' => array_keys($data),
            ]);

            // Hapus field yang tidak perlu disimpan ke tabel purchases
            unset($data['vehicle_photos']);
            unset($data['brand_name']);
            unset($data['vehicle_model_name']);
            unset($data['type_name']);
            unset($data['color_name']);
            unset($data['year_name']);
            unset($data['vehicle_notes']);
            unset($data['engine_specification']);
            unset($data['location']);
            unset($data['odometer']);
            unset($data['vin']);
            unset($data['engine_number']);
            unset($data['license_plate']);
            unset($data['bpkb_number']);

            //  LOG 8: Data final yang akan disimpan ke purchases
            Log::info('=== DATA FINAL UNTUK PURCHASES ===', [
                'all_keys_after' => array_keys($data),
                'final_data' => $data,
            ]);

        } catch (\Exception $e) {
            //  LOG ERROR
            Log::error('=== ERROR SAAT CREATE PURCHASE ===', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }

        return $data;
    }

    //  LOG 9: Setelah berhasil create
    protected function afterCreate(): void
    {
        Log::info('=== PURCHASE CREATED SUCCESSFULLY ===', [
            'purchase_id' => $this->record->id,
            'vehicle_id' => $this->record->vehicle_id,
        ]);
    }

    //  LOG 10: Jika ada validation error
    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Log::error('=== VALIDATION ERROR ===', [
            'errors' => $exception->errors(),
            'message' => $exception->getMessage(),
        ]);

        parent::onValidationError($exception);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Data Pembelian Berhasil Disimpan!')
            ->body('Kendaraan baru berhasil ditambahkan ke daftar.')
            ->success();
    }
}
