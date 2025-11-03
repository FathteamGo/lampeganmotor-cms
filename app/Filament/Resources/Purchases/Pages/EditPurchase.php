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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $vehicle = Vehicle::find($this->record->vehicle_id);

        if ($vehicle) {
            $vehicle->update([
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
            ]);
        }

        // 🔹 Update total harga juga
        $additionalCosts = collect($data['additional_costs'] ?? [])
            ->sum(fn($item) => floatval($item['price'] ?? 0));
        $data['total_price'] = floatval($data['purchase_price']) + $additionalCosts;

        return $data;
    }
}
