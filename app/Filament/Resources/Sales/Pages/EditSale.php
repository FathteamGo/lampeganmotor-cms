<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use App\Models\Customer;
use Filament\Resources\Pages\EditRecord;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    /**
     * Load data customer ke form saat buka halaman edit
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->customer) {
            $data['customer_name']      = $this->record->customer->name;
            $data['customer_nik']       = $this->record->customer->nik;
            $data['customer_phone']     = $this->record->customer->phone;
            $data['customer_address']   = $this->record->customer->address;
            $data['customer_instagram'] = $this->record->customer->instagram;
            $data['customer_tiktok']    = $this->record->customer->tiktok;
        }

        return $data;
    }

    /**
     * Update customer sebelum save sale
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update data customer
        if ($this->record->customer_id && !empty($data['customer_name'])) {
            Customer::where('id', $this->record->customer_id)->update([
                'name'      => trim($data['customer_name']),
                'nik'       => $data['customer_nik'] ?? null,
                'phone'     => $data['customer_phone'] ?? null,
                'address'   => $data['customer_address'] ?? null,
                'instagram' => $data['customer_instagram'] ?? null,
                'tiktok'    => $data['customer_tiktok'] ?? null,
            ]);
        }

        // Hapus field customer_* dari data sale
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