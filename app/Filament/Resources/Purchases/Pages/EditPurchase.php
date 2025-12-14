<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Vehicle;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

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
        // 🔹 Ambil data vehicle kalau ada
        if ($this->record->vehicle_id) {
            $vehicle = Vehicle::with(['vehicleModel.brand', 'type', 'color', 'year'])->find($this->record->vehicle_id);
            
            if ($vehicle) {
                // Data dasar kendaraan
                $data['brand_name'] = $vehicle->vehicleModel->brand->name ?? '';
                $data['vehicle_model_name'] = $vehicle->vehicleModel->name ?? '';
                $data['type_name'] = $vehicle->type->name ?? '';
                $data['color_name'] = $vehicle->color->name ?? '';
                $data['year_name'] = $vehicle->year->year ?? '';
                
                // Nomor-nomor
                $data['vin'] = $vehicle->vin;
                $data['engine_number'] = $vehicle->engine_number;
                $data['license_plate'] = $vehicle->license_plate;
                $data['bpkb_number'] = $vehicle->bpkb_number;
                
                // Info tambahan
                $data['engine_specification'] = $vehicle->engine_specification;
                $data['location'] = $vehicle->location;
                $data['vehicle_notes'] = $vehicle->notes;
                
                // 🔹 Format angka untuk ditampilkan dengan titik ribuan
                $data['purchase_price'] = $vehicle->purchase_price 
                    ? number_format($vehicle->purchase_price, 0, ',', '.') 
                    : '0';
                $data['sale_price'] = $vehicle->sale_price 
                    ? number_format($vehicle->sale_price, 0, ',', '.') 
                    : '0';
                $data['down_payment'] = $vehicle->down_payment 
                    ? number_format($vehicle->down_payment, 0, ',', '.') 
                    : '0';
                $data['odometer'] = $vehicle->odometer 
                    ? number_format($vehicle->odometer, 0, ',', '.') 
                    : null;
            }
        }
        
        // 🔹 Format biaya tambahan dengan BENAR (jangan sampai 200000 jadi 20.0000)
        if (!empty($data['additional_costs'])) {
            foreach ($data['additional_costs'] as &$cost) {
                if (isset($cost['price']) && is_numeric($cost['price'])) {
                    // Pastikan angka murni dulu baru format
                    $cleanPrice = floatval($cost['price']);
                    $cost['price'] = number_format($cleanPrice, 0, ',', '.');
                }
            }
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 🔹 Clean format ribuan dari semua field harga
        $cleanPrice = fn($value) => isset($value) ? preg_replace('/[^0-9]/', '', $value) : null;
        
        $vehicle = Vehicle::find($this->record->vehicle_id);

        if ($vehicle) {
            // 🔹 Clean & update data kendaraan
            $cleanData = [
                'vin' => $data['vin'] ?? $vehicle->vin,
                'engine_number' => $data['engine_number'] ?? $vehicle->engine_number,
                'license_plate' => $data['license_plate'] ?? null,
                'bpkb_number' => $data['bpkb_number'] ?? null,
                'purchase_price' => $cleanPrice($data['purchase_price'] ?? $vehicle->purchase_price),
                'sale_price' => $cleanPrice($data['sale_price'] ?? 0),
                'down_payment' => $cleanPrice($data['down_payment'] ?? 0),
                'odometer' => $cleanPrice($data['odometer'] ?? 0),
                'engine_specification' => $data['engine_specification'] ?? null,
                'notes' => $data['vehicle_notes'] ?? null,
                'location' => $data['location'] ?? null,
            ];

            $vehicle->update($cleanData);
        }

        // 🧹 Clean biaya tambahan dengan BENAR
        if (!empty($data['additional_costs'])) {
            foreach ($data['additional_costs'] as &$cost) {
                if (isset($cost['price'])) {
                    // Clean angka (hilangkan titik dan koma)
                    $cost['price'] = preg_replace('/[^0-9]/', '', $cost['price']);
                }
            }
        }

        // 🔹 Update total harga purchase
        $additionalCosts = collect($data['additional_costs'] ?? [])
            ->sum(fn($item) => floatval($item['price'] ?? 0));
        
        $purchasePrice = $cleanPrice($data['purchase_price'] ?? 0);
            
        $data['total_price'] = floatval($purchasePrice) + $additionalCosts;

        return $data;
    }
}