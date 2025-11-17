<?php

namespace App\Filament\Resources\StnkRenewals\Pages;

use App\Filament\Resources\StnkRenewals\StnkRenewalResource;
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

        return $data;
    }
}
