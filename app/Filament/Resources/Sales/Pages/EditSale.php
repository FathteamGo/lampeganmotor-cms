<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use App\Models\Customer;
use App\Models\Sale;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

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
     * Update customer dan validasi status sebelum save sale
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

        // --- LOGIKA STATUS ---

        $newStatus = $data['status'] ?? null;
        $currentStatus = $this->record->status;

        // Jika record ini sudah cancel, jangan ubah statusnya
        if ($currentStatus === 'cancel' && $newStatus && $newStatus !== 'cancel') {
            session()->flash('error', 'Status motor ini sudah dibatalkan dan tidak bisa diubah lagi.');
            throw ValidationException::withMessages([
                'status' => "Status motor yang sudah dibatalkan tidak bisa diubah lagi.",
            ]);
        }

        // Cek duplicate untuk status aktif (proses, kirim, selesai) di motor yang sama
        if ($newStatus && in_array($newStatus, ['proses', 'kirim', 'selesai'])) {
            $existingActive = Sale::where('vehicle_id', $this->record->vehicle_id)
                ->whereIn('status', ['proses', 'kirim', 'selesai'])
                ->where('id', '!=', $this->record->id)
                ->first();

            if ($existingActive) {
                session()->flash('warning', "Motor ini sudah dijual kepada customer: {$existingActive->customer_name}");
                throw ValidationException::withMessages([
                    'status' => "Motor ini sudah dijual kepada customer: {$existingActive->customer_name}.",
                ]);
            }
        }

        // Tambahkan catatan otomatis jika status cancel
        if ($newStatus && $newStatus === 'cancel') {
            $data['notes'] = trim(($data['notes'] ?? '') . "\n[Dibatalkan pada " . now()->format('d M Y H:i') . "]");
            session()->flash('info', 'Status diubah menjadi CANCEL, catatan otomatis ditambahkan.');
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

    /**
     * Redirect setelah save
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
