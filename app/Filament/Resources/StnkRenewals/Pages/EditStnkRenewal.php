<?php

namespace App\Filament\Resources\StnkRenewals\Pages;

use App\Filament\Resources\StnkRenewals\StnkRenewalResource;
use App\Models\Customer;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStnkRenewal extends EditRecord
{
    protected static string $resource = StnkRenewalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Format nominal ketika tampil di form edit
     * TANPA merusak nilai asli dari DB
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $nominalFields = [
            'total_pajak_jasa',
            'dp',
            'payvendor',
            'pembayaran_ke_samsat',
            'sisa_pembayaran',
            'margin_total',
        ];

        foreach ($nominalFields as $field) {
            if (isset($data[$field]) && $data[$field] !== null) {

                // DB simpan integer, jadi cukup cast ke int
                $value = (int) $data[$field];

                // Format untuk tampilan
                $data[$field] = number_format($value, 0, ',', '.');
            }
        }

        // =============================
        // Load data customer ke form
        // =============================
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
     * Update customer sebelum simpan
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['customer_name'])) {
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

            // Set customer_id ke stnk_renewals
            $data['customer_id'] = $customer->id;
        }

        // Hapus field customer_* agar tidak tersimpan ke table stnk_renewals
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
}
