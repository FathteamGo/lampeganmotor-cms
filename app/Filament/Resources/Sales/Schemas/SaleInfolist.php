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
            TextEntry::make('customer.name')->label('Nama Customer'),
            TextEntry::make('customer.address')->label('Alamat'),
            TextEntry::make('customer.phone')->label('No Telepon'),

            // Kendaraan
            TextEntry::make('vehicle.vehicleModel.name')->label('Jenis Motor'),
            TextEntry::make('vehicle.type.name')->label('Type'),
            TextEntry::make('vehicle.year.year')->label('Tahun'),
            TextEntry::make('vehicle.color.name')->label('Warna'),
            TextEntry::make('vehicle.license_plate')->label('No Pol'),

            // Detail Penjualan
            TextEntry::make('sale_date')->label('Tanggal Penjualan')->date(),
            TextEntry::make('payment_method')
                ->label('Metode Pembayaran')
                ->badge()
                ->color(fn($state) => match($state) {
                    'cash' => 'success',
                    'credit' => 'info',
                    'cash_tempo' => 'warning',
                    'tukartambah' => 'primary',
                    default => 'gray'
                })
                ->formatStateUsing(function ($state) {
                    return match($state) {
                        'cash' => 'Cash',
                        'credit' => 'Credit',
                        'tukartambah' => 'Tukar Tambah',
                        'cash_tempo' => 'Cash Tempo',
                        default => $state
                    };
                }),
            TextEntry::make('leasing')
                ->label('Leasing')
                ->badge()
                ->color('info')
                ->visible(fn($record) => $record->payment_method === 'credit'),
            TextEntry::make('due_date')
                ->label('Jatuh Tempo')
                ->date()
                ->color('warning')
                ->visible(fn($record) => in_array($record->payment_method, ['credit', 'cash_tempo'])),
            TextEntry::make('order_source')->label('Sumber Order'),
            TextEntry::make('user.name')->label('Sales / Ex'),
            TextEntry::make('branch_name')->label('Cabang'),
            TextEntry::make('result')->label('Hasil')->badge(),
            TextEntry::make('status')
                ->label('Status')
                ->badge()
                ->colors([
                    'warning' => fn($state) => in_array($state, ['selesai']),
                    'success' => fn($state) => in_array($state, ['kirim']),
                    'info'    => fn($state) => in_array($state, ['proses']),
                    'danger'  => fn($state) => in_array($state, ['cancel']),
                ]),

            // Harga - Simple
            TextEntry::make('total_pembelian')
                ->label('Modal')
                ->money('IDR', locale: 'id')
                ->color('danger')
                ->weight('bold')
                ->state(function ($record) {
                    // Coba ambil dari purchase.grand_total dulu
                    $purchase = \App\Models\Purchase::where('vehicle_id', $record->vehicle_id)->first();
                    $grandTotal = $purchase ? $purchase->grand_total : 0;
                    
                    // Kalau grand_total = 0, fallback ke vehicle.purchase_price
                    if ($grandTotal == 0) {
                        return $record->vehicle?->purchase_price ?? 0;
                    }
                    
                    return $grandTotal;
                }),
            
            TextEntry::make('sale_price')
                ->label('Harga Jual (OTR)')
                ->money('IDR', locale: 'id')
                ->color('success')
                ->weight('bold'),
            
            TextEntry::make('dp_po')
                ->label('DP PO')
                ->money('IDR', locale: 'id')
                ->visible(fn($record) => in_array($record->payment_method, ['credit', 'cash_tempo']) && $record->dp_po > 0),
            
            TextEntry::make('dp_real')
                ->label('DP REAL')
                ->money('IDR', locale: 'id')
                ->visible(fn($record) => in_array($record->payment_method, ['credit', 'cash_tempo']) && $record->dp_real > 0),
            
            TextEntry::make('remaining_payment')
                ->label('Sisa Pembayaran')
                ->money('IDR', locale: 'id')
                ->color('warning')
                ->weight('bold')
                ->visible(fn($record) => in_array($record->payment_method, ['credit', 'cash_tempo'])),

            // Laba - Simple & Jelas
            TextEntry::make('laba_kotor')
                ->label('Laba Kotor')
                ->money('IDR', locale: 'id')
                ->color(fn($state) => $state < 0 ? 'danger' : 'success')
                ->weight('bold')
                ->size('lg')
                ->state(function ($record) {
                    return self::calculateLabaKotor($record);
                }),
            
            TextEntry::make('cmo.name')
                ->label('CMO / Mediator')
                ->visible(fn($record) => $record->cmo_id),
            
            TextEntry::make('cmo_fee')
                ->label('Fee CMO')
                ->money('IDR', locale: 'id')
                ->color('danger')
                ->visible(fn($record) => ($record->cmo_fee ?? 0) > 0),
            
            TextEntry::make('direct_commission')
                ->label('Komisi Langsung')
                ->money('IDR', locale: 'id')
                ->color('danger')
                ->visible(fn($record) => ($record->direct_commission ?? 0) > 0),
            
            TextEntry::make('laba_bersih')
                ->label('Laba Bersih')
                ->money('IDR', locale: 'id')
                ->color(fn($state) => $state < 0 ? 'danger' : 'success')
                ->weight('bold')
                ->size('xl')
                ->state(function ($record) {
                    $labaKotor = self::calculateLabaKotor($record);
                    $pengeluaran = ($record->cmo_fee ?? 0) + ($record->direct_commission ?? 0);
                    return $labaKotor - $pengeluaran;
                }),

            // Catatan & Waktu
            TextEntry::make('notes')->label('Catatan')->columnSpanFull(),
            TextEntry::make('created_at')->label('Dibuat')->dateTime(),
            TextEntry::make('updated_at')->label('Terakhir Diubah')->dateTime(),
        ]);
    }

    /**
     * Hitung Laba Kotor berdasarkan metode pembayaran
     * 
     * RUMUS:
     * - Credit: OTR - DP PO - DP REAL - Modal
     * - Cash/Cash Tempo/Tukar Tambah: OTR - Modal
     * 
     * Modal = Harga motor + biaya tambahan (STNK, pajak, dll)
     * Kalau data purchase tidak ada atau 0, pakai vehicle.purchase_price
     * Laba Bersih = Laba Kotor - Fee CMO - Komisi Langsung
     */
    private static function calculateLabaKotor($record): float
    {
        $otr = $record->sale_price ?? 0;
        $dpPo = $record->dp_po ?? 0;
        $dpReal = $record->dp_real ?? 0;
        
        // Ambil modal (harga motor + biaya tambahan)
        $purchase = \App\Models\Purchase::where('vehicle_id', $record->vehicle_id)->first();
        $modal = $purchase ? $purchase->grand_total : 0;
        
        // Fallback: kalau grand_total = 0, pakai vehicle.purchase_price
        if ($modal == 0) {
            $modal = $record->vehicle?->purchase_price ?? 0;
        }

        // Hitung laba kotor
        return match($record->payment_method) {
            'credit' => $otr - $dpPo + $dpReal - $modal,
            default => $otr - $modal
        };
    }
}