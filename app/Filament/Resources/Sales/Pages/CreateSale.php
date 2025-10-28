<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use App\Models\Customer;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    /**
     * Handle form data sebelum save
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Validasi nama customer wajib ada
        if (empty($data['customer_name'])) {
            Notification::make()
                ->title('Error!')
                ->body('Nama customer wajib diisi')
                ->danger()
                ->send();
            
            $this->halt();
        }

        // Create atau update customer
        $customer = Customer::updateOrCreate(
            [
                'name'  => trim($data['customer_name']),
                'phone' => !empty($data['customer_phone']) ? trim($data['customer_phone']) : null,
            ],
            [
                'nik'       => $data['customer_nik'] ?? null,
                'address'   => $data['customer_address'] ?? null,
                'instagram' => $data['customer_instagram'] ?? null,
                'tiktok'    => $data['customer_tiktok'] ?? null,
            ]
        );

        // Set customer_id
        $data['customer_id'] = $customer->id;

        // Hapus field customer_* (karena gak ada di table sales)
        unset(
            $data['customer_name'],
            $data['customer_nik'],
            $data['customer_phone'],
            $data['customer_address'],
            $data['customer_instagram'],
            $data['customer_tiktok']
        );

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}