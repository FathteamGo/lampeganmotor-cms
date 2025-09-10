<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('customer.name')->label('Nama Customer'),
            TextEntry::make('customer.address')->label('Alamat'),
            TextEntry::make('customer.phone')->label('No Telepon'),

            TextEntry::make('vehicle.vehicleModel.name')->label('Jenis Motor'),
            TextEntry::make('vehicle.type.name')->label('Type'),
            TextEntry::make('vehicle.year.year')->label('Tahun'),
            TextEntry::make('vehicle.color.name')->label('Warna'),
            TextEntry::make('vehicle.license_plate')->label('No Pol'),

            TextEntry::make('vehicle.purchase_price')->label('H TOTAL PEMBELIAN')->money('IDR', locale: 'id'),
            TextEntry::make('vehicle.sale_price')->label('OTR')->money('IDR', locale: 'id'),

            TextEntry::make('dp_po_calc')
                ->label('DP PO')
                ->state(fn ($record) => optional($record->vehicle)->dp_percentage
                    ? round((($record->vehicle->sale_price ?? $record->sale_price ?? 0) * ($record->vehicle->dp_percentage / 100)))
                    : null
                )
                ->money('IDR', locale: 'id'),

            TextEntry::make('dp_real_calc')
                ->label('DP REAL')
                ->state(fn ($record) => $record->payment_method === 'cash_tempo'
                    ? max(0, (int) ($record->sale_price - (int) ($record->remaining_payment ?? 0)))
                    : null
                )
                ->money('IDR', locale: 'id'),

            TextEntry::make('pencairan')->label('PENCAIRAN')->money('IDR', locale: 'id'),

            TextEntry::make('sale_date')->label('Tanggal Jual')->date(),
            TextEntry::make('sale_price')->label('TOTAL PENJUALAN')->money('IDR', locale: 'id'),

            TextEntry::make('laba_bersih')->label('LABA BERSIH')->money('IDR', locale: 'id'),

            TextEntry::make('payment_method')
                ->label('Metode Pembayaran')
                ->formatStateUsing(fn ($s) => match ($s) {
                    'cash' => 'Cash',
                    'credit' => 'Credit',
                    'tukartambah' => 'Tukar Tambah',
                    'cash_tempo' => 'Cash Tempo',
                    default => $s ?: '-',
                }),

            TextEntry::make('remaining_payment')->label('Sisa Pembayaran')->money('IDR', locale: 'id'),
            TextEntry::make('due_date')->label('Jatuh Tempo')->date(),

            TextEntry::make('cmo')->label('CMO / Mediator'),
            TextEntry::make('cmo_fee')->label('Fee CMO')->money('IDR', locale: 'id'),
            TextEntry::make('komisi_langsung')->label('Komisi Langsung')->money('IDR', locale: 'id'),

            TextEntry::make('user.name')->label('Ex'),
            TextEntry::make('branch_name')->label('Cabang'),
            TextEntry::make('result')->label('Hasil'),

            TextEntry::make('order_source')
                ->label('Sumber Order')
                ->formatStateUsing(fn ($s) => match ($s) {
                    'fb' => 'Facebook',
                    'ig' => 'Instagram',
                    'tiktok' => 'TikTok',
                    'walk_in' => 'Walk In',
                    default => $s ?: '-',
                }),

            TextEntry::make('status')
                ->label('Status')
                ->formatStateUsing(fn ($s) => match ($s) {
                    'proses' => 'Proses',
                    'kirim' => 'Kirim',
                    'selesai' => 'Selesai',
                    default => $s ?: '-',
                }),

            TextEntry::make('notes')->label('Catatan'),

            TextEntry::make('created_at')->label('Dibuat')->dateTime(),
            TextEntry::make('updated_at')->label('Diubah')->dateTime(),
        ]);
    }
}
