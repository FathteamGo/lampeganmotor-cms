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
        if (!empty($data['additionalCosts'])) {
            foreach ($data['additionalCosts'] as &$cost) {
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
    $vehicle = Vehicle::find($this->record->vehicle_id);

    if ($vehicle) {

        $updateData = [];
        
        if (isset($data['license_plate'])) {
            $updateData['license_plate'] = $data['license_plate'];
        }
        if (isset($data['bpkb_number'])) {
            $updateData['bpkb_number'] = $data['bpkb_number'];
        }
        if (isset($data['purchase_price'])) {
            $updateData['purchase_price'] = $data['purchase_price'];
        }
        if (isset($data['sale_price'])) {
            $updateData['sale_price'] = $data['sale_price'];
        }
        if (isset($data['down_payment'])) {
            $updateData['down_payment'] = $data['down_payment'];
        }
        if (isset($data['odometer'])) {
            $updateData['odometer'] = $data['odometer'];
        }
        if (isset($data['engine_specification'])) {
            $updateData['engine_specification'] = $data['engine_specification'];
        }
        if (isset($data['location'])) {
            $updateData['location'] = $data['location'];
        }
        if (isset($data['vehicle_notes'])) {
            $updateData['notes'] = $data['vehicle_notes'];
        }

        // Only update if there's data to update
        if (!empty($updateData)) {
            $vehicle->update($updateData);
        }
    }

    return $data;
}

}