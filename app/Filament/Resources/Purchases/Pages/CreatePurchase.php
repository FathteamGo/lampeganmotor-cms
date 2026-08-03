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
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Buatkan logic jika category additional costs tidak ada make default Tidak Ada
        if (empty($data['additionalCosts'])) {
            $data['additionalCosts'] = [
                [
                    'category' => 'Tidak Ada',
                    'price' => 0,
                ],
            ];
        }

        try {
            // Cek apakah kendaraan sudah terdaftar (buyback / restock)
            $exist = Vehicle::where('vin', $data['vin'])->exists()
                || Vehicle::where('engine_number', $data['engine_number'])->exists();

            // Buat atau ambil data master
            $brand = Brand::firstOrCreate(['name' => $data['brand_name']]);

            $model = VehicleModel::firstOrCreate([
                'name' => $data['vehicle_model_name'],
                'brand_id' => $brand->id,
            ]);

            $type = Type::firstOrCreate(['name' => $data['type_name']]);
            $color = Color::firstOrCreate(['name' => $data['color_name']]);
            $year = Year::firstOrCreate(['year' => $data['year_name']]);

            if ($exist == false) {
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
                            ->orWhere('engine_number', $data['engine_number'])
                            ->first();

                // Cek apakah kendaraan masih punya penjualan yang sedang berjalan.
                // Jika ada, batalkan buyback. Sale 'selesai' adalah syarat sah buyback.
                // Key error HARUS ber-prefix 'data.' — itu state path form Filament,
                // tanpa prefix pesannya tidak akan tampil di sebelah field.
                $runningSale = $vehicle->runningSale();

                if ($runningSale) {
                    throw ValidationException::withMessages([
                        'data.vin' => "Motor ini masih dalam penjualan berjalan atas nama "
                               . ($runningSale->customer?->name ?? 'customer') . " (status: {$runningSale->status}). "
                               . "Selesaikan atau batalkan penjualan tersebut sebelum melakukan pembelian kembali.",
                    ]);
                }

                // UPDATE DATA KENDARAAN EXISTING (BUYBACK / RESTOCK)
                $updateData = [
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
                ];

                // Buyback disetujui — motor kembali jadi stok showroom
                $updateData['status'] = 'available';

                $vehicle->update($updateData);

                Log::info('Buyback disetujui, status kendaraan kembali available', [
                    'vehicle_id' => $vehicle->id,
                    'license_plate' => $vehicle->license_plate,
                ]);
            }

            if (!empty($data['vehicle_photos'])) {
                foreach ($data['vehicle_photos'] as $photoData) {
                    if (!empty($photoData['path'])) {
                        VehiclePhoto::create([
                            'vehicle_id' => $vehicle->id,
                            'path' => $photoData['path'],
                            'caption' => $photoData['caption'] ?? null,
                        ]);
                    }
                }
            }

            // Set vehicle_id untuk Purchase
            $data['vehicle_id'] = $vehicle->id;

            $additionalTotal = collect($data['additionalCosts'] ?? [])
                ->sum(fn ($item) => intval($item['price'] ?? 0));

            // Hitung total_price (harga beli + biaya tambahan)
            $data['total_price'] = intval($data['purchase_price']) + $additionalTotal;

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

        } catch (ValidationException $e) {
            // Penolakan yang sah (mis. buyback motor yang masih dalam pengiriman).
            // Bukan error aplikasi — jangan dicatat sebagai error.
            throw $e;
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan pembelian', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }

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
