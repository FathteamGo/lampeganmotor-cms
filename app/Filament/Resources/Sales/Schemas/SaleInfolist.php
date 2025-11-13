<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // 🔹 Customer
            TextEntry::make('customer.name')->label('Nama'),
            TextEntry::make('customer.address')->label('Alamat'),
            TextEntry::make('customer.phone')->label('No Telepon'),

            // 🔹 Kendaraan
            TextEntry::make('vehicle.vehicleModel.name')->label('Jenis Motor'),
            TextEntry::make('vehicle.type.name')->label('Type'),
            TextEntry::make('vehicle.year.year')->label('Tahun'),
            TextEntry::make('vehicle.color.name')->label('Warna'),
            TextEntry::make('vehicle.license_plate')->label('No Pol'),

            // 🔹 Harga & Perhitungan
            TextEntry::make('vehicle.purchase_price')->label('H-Total Pembelian')->money('IDR', locale: 'id'),
            TextEntry::make('sale_price')->label('OTR')->money('IDR', locale: 'id'),
            TextEntry::make('dp_po')->label('DP PO')->money('IDR', locale: 'id'),
            TextEntry::make('dp_real')->label('DP REAL')->money('IDR', locale: 'id'),
            TextEntry::make('pencairan')->label('Pencairan')->money('IDR', locale: 'id'),

            // 🔹 Laba Bersih (untuk semua metode pembayaran)
            TextEntry::make('laba_bersih')
                ->label('Laba Bersih')
                ->money('IDR', locale: 'id')
                ->state(function ($record) {
                    $pencairan = $record->pencairan ?? $record->sale_price ?? 0;
                    $dpReal = $record->dp_real ?? 0;
                    $beli = $record->vehicle?->purchase_price ?? 0;
                    $cmoFee = $record->cmo_fee ?? 0;
                    $komisi = $record->direct_commission ?? 0;

                    return max(($pencairan + $dpReal) - ($beli + $cmoFee + $komisi), 0);
                }),

            // 🔹 Komisi & CMO
            TextEntry::make('cmo')->label('CMO'),
            TextEntry::make('cmo_fee')->label('Fee CMO')->money('IDR', locale: 'id'),
            TextEntry::make('direct_commission')->label('Komisi Langsung')->money('IDR', locale: 'id'),

            // 🔹 Info Umum
            TextEntry::make('payment_method')->label('Metode Pembayaran'),
            TextEntry::make('order_source')->label('Sumber Order'),
            TextEntry::make('user.name')->label('Sales / Ex'),
            TextEntry::make('branch_name')->label('Cabang'),
            TextEntry::make('result')->label('Hasil'),
            TextEntry::make('status')
                ->label('Status')
                ->badge()
                ->colors([
                    'warning' => fn($state) => in_array($state, ['selesai']),
                    'success' => fn($state) => in_array($state, ['kirim']),
                    'info'    => fn($state) => in_array($state, ['proses']),
                    'danger'  => fn($state) => in_array($state, ['cancel']),
                ]),
            TextEntry::make('notes')->label('Catatan'),

            // 🔹 Waktu
            TextEntry::make('sale_date')->label('Tanggal Penjualan')->date(),
            TextEntry::make('created_at')->label('Dibuat')->dateTime(),
            TextEntry::make('updated_at')->label('Diubah')->dateTime(),
        ]);
    }
}
