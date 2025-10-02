<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // Customer
            TextEntry::make('customer.name')->label('Nama'),
            TextEntry::make('customer.address')->label('Alamat'),
            TextEntry::make('customer.phone')->label('No Telepon'),

            // Kendaraan
            TextEntry::make('vehicle.vehicleModel.name')->label('Jenis Motor'),
            TextEntry::make('vehicle.type.name')->label('Type'),
            TextEntry::make('vehicle.year.year')->label('Tahun'),
            TextEntry::make('vehicle.color.name')->label('Warna'),
            TextEntry::make('vehicle.license_plate')->label('No Pol'),

            // Harga
            TextEntry::make('vehicle.purchase_price')
                ->label('H-Total Pembelian')
                ->money('IDR', locale: 'id'),

            TextEntry::make('sale_price')
                ->label('OTR')
                ->money('IDR', locale: 'id'),

            TextEntry::make('dp_po')
                ->label('Dp Po')
                ->money('IDR', locale: 'id'),

            TextEntry::make('dp_real')
                ->label('Dp Real')
                ->money('IDR', locale: 'id'),

            TextEntry::make('pencairan')
                ->label('Pencairan')
                ->money('IDR', locale: 'id'),

            TextEntry::make('laba_bersih')
                ->label('Laba Bersih')
                ->money('IDR', locale: 'id')
                ->state(fn($record) => max(
                    // logic: pencairan (atau sale_price jika pencairan kosong) + dp_real
                    // dikurangi purchase_price, cmo_fee, direct_commission
                    ($record->pencairan ?? $record->sale_price ?? 0)
                    + ($record->dp_real ?? 0)
                    - ($record->vehicle?->purchase_price ?? 0)
                    - ($record->cmo_fee ?? 0)
                    - ($record->direct_commission ?? 0),
                    0
                )),

            // Pembayaran
            TextEntry::make('payment_method')
                ->label('Metode Pembayaran')
                ->formatStateUsing(fn($s) => match ($s) {
                    'cash' => 'Cash',
                    'credit' => 'Credit',
                    'tukartambah' => 'Tukar Tambah',
                    'cash_tempo' => 'Cash Tempo',
                    default => $s ?: '-',
                }),

            TextEntry::make('remaining_payment')
                ->label('Sisa Pembayaran')
                ->money('IDR', locale: 'id')
                ->state(fn($record) => match ($record->payment_method) {
                    'credit', 'cash_tempo' => max(0, ($record->sale_price ?? 0) - ($record->dp_real ?? 0)),
                    default => null,
                }),

            TextEntry::make('due_date')
                ->label('Jatuh Tempo')
                ->date()
                ->state(fn($record) => match ($record->payment_method) {
                    'credit', 'cash_tempo' => ($record->created_at ? $record->created_at->copy()->addDays(30) : null),
                    default => null,
                }),

            // CMO & Komisi
            TextEntry::make('cmo')->label('CMO'),
            TextEntry::make('cmo_fee')->label('FEE CMO')->money('IDR', locale: 'id'),
            TextEntry::make('direct_commission')->label('Komisi Langsung')->money('IDR', locale: 'id'),

            // Lainnya
            TextEntry::make('order_source')->label('Sumber Order'),
            TextEntry::make('user.name')->label('Ex'),
            TextEntry::make('branch_name')->label('Cabang'),
            TextEntry::make('result')->label('Hasil'),
            TextEntry::make('status')->label('Status'),
            TextEntry::make('notes')->label('Note'),

            // Timestamps
            TextEntry::make('sale_date')->label('Tanggal')->date(),
            TextEntry::make('created_at')->label('Dibuat')->dateTime(),
            TextEntry::make('updated_at')->label('Diubah')->dateTime(),
        ]);
    }
}
