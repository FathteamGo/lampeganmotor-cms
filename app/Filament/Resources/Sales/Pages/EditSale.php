<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use App\Models\Customer;
use App\Models\Vehicle;
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
            $customer = Customer::find($this->record->customer_id);
            if ($customer) {
                $customer->name = trim($data['customer_name']);

                // No. telepon ikut field form apa adanya (boleh dikosongkan),
                // karena field-nya memang selalu ada di form penjualan.
                if (array_key_exists('customer_phone', $data)) {
                    $customer->phone = filled($data['customer_phone']) ? trim($data['customer_phone']) : null;
                }

                // Sisanya hanya ditimpa kalau benar-benar diisi — jangan sampai
                // data master customer terhapus hanya karena field dikosongkan.
                foreach (['nik', 'address', 'instagram', 'tiktok'] as $field) {
                    if (!empty($data["customer_{$field}"])) {
                        $customer->{$field} = $data["customer_{$field}"];
                    }
                }
                $customer->save();
            }
        }

        // --- LOGIKA STATUS ---

        $newStatus = $data['status'] ?? null;
        $currentStatus = $this->record->status;

        // Jika record ini sudah cancel, jangan ubah statusnya.
        // Key error HARUS ber-prefix 'data.' (state path form Filament),
        // tanpa itu pesannya tidak tampil di sebelah field.
        if ($currentStatus === 'cancel' && $newStatus && $newStatus !== 'cancel') {
            throw ValidationException::withMessages([
                'data.status' => "Status motor yang sudah dibatalkan tidak bisa diubah lagi.",
            ]);
        }

        // Cek duplicate untuk status aktif (proses, kirim, selesai) di motor yang sama
        if ($newStatus && in_array($newStatus, Vehicle::LOCKING_SALE_STATUSES)) {
            $running = Vehicle::find($this->record->vehicle_id)
                ?->runningSale(exceptSaleId: $this->record->id);

            if ($running) {
                throw ValidationException::withMessages([
                    'data.status' => "Motor ini sedang dalam penjualan berjalan atas nama "
                              . ($running->customer?->name ?? 'customer') . ".",
                ]);
            }
        }

        // Hitung ulang purchase_id — motor dan/atau tanggal jual bisa diubah di sini,
        // dan laba harus tetap mengacu ke pembelian yang benar.
        $vehicleId = $data['vehicle_id'] ?? $this->record->vehicle_id;
        $resolvedPurchaseId = Vehicle::find($vehicleId)
            ?->purchaseForSaleDate($data['sale_date'] ?? $this->record->sale_date)?->id;

        // Jangan hapus purchase_id yang sudah ada hanya karena lookup gagal,
        // kecuali motornya memang diganti.
        if ($resolvedPurchaseId || (int) $vehicleId !== (int) $this->record->vehicle_id) {
            $data['purchase_id'] = $resolvedPurchaseId;
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
