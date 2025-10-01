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
            TextEntry::make('customer.name')->label('Nama')->toggleable(),
            TextEntry::make('customer.address')->label('Alamat')->toggleable(),
            TextEntry::make('customer.phone')->label('No Telepon')->toggleable(),

            // Kendaraan
            TextEntry::make('vehicle.vehicleModel.name')->label('Jenis Motor')->toggleable(),
            TextEntry::make('vehicle.type.name')->label('Type')->toggleable(),
            TextEntry::make('vehicle.year.year')->label('Tahun')->toggleable(),
            TextEntry::make('vehicle.color.name')->label('Warna')->toggleable(),
            TextEntry::make('vehicle.license_plate')->label('No Pol')->toggleable(),

            // Harga
            TextEntry::make('vehicle.purchase_price')->label('H-Total Pembelian')->money('IDR', locale: 'id')->toggleable(),
            TextEntry::make('sale_price')->label('OTR')->money('IDR', locale: 'id')->toggleable(),
            TextEntry::make('dp_po')->label('Dp Po')->money('IDR', locale: 'id')->toggleable(),
            TextEntry::make('dp_real')->label('Dp Real')->money('IDR', locale: 'id')->toggleable(),
            TextEntry::make('pencairan')->label('Pencairan')->money('IDR', locale: 'id')->toggleable(),
            TextEntry::make('laba_bersih')
                ->label('Laba Bersih')
                ->money('IDR', locale: 'id')
                ->toggleable()
                ->state(fn($record) => max(
                    ($record->pencairan ?? $record->sale_price ?? 0) + ($record->dp_real ?? 0)
                    - ($record->vehicle->purchase_price ?? 0)
                    - ($record->cmo_fee ?? 0)
                    - ($record->direct_commission ?? 0),
                    0
                )),

            // Pembayaran
            TextEntry::make('payment_method')
                ->label('Metode Pembayaran')
                ->toggleable()
                ->formatStateUsing(fn($s) => match($s){
                    'cash' => 'Cash',
                    'credit' => 'Credit',
                    'tukartambah' => 'Tukar Tambah',
                    'cash_tempo' => 'Cash Tempo',
                    default => $s ?: '-',
                }),

            TextEntry::make('remaining_payment')
                ->label('Sisa Pembayaran')
                ->money('IDR', locale: 'id')
                ->toggleable()
                ->state(fn($record) => match($record->payment_method){
                    'credit', 'cash_tempo' => max(0, ($record->sale_price ?? 0) - ($record->dp_real ?? 0)),
                    default => null,
                }),

            TextEntry::make('due_date')
                ->label('Jatuh Tempo')
                ->date()
                ->toggleable()
                ->state(fn($record) => match($record->payment_method){
                    'credit', 'cash_tempo' => optional($record->created_at)->addDays(30),
                    default => null,
                }),

            // CMO & Komisi
            TextEntry::make('cmo')->label('CMO')->toggleable(),
            TextEntry::make('cmo_fee')->label('FEE CMO')->money('IDR', locale: 'id')->toggleable(),
            TextEntry::make('direct_commission')->label('Komisi Langsung')->money('IDR', locale: 'id')->toggleable(),

            // Lainnya
            TextEntry::make('order_source')->label('Sumber Order')->toggleable(),
            TextEntry::make('user.name')->label('Ex')->toggleable(),
            TextEntry::make('branch_name')->label('Cabang')->toggleable(),
            TextEntry::make('result')->label('Hasil')->toggleable(),
            TextEntry::make('status')->label('Status')->toggleable(),
            TextEntry::make('notes')->label('Note')->toggleable(),

            // Timestamps
            TextEntry::make('sale_date')->label('Tanggal')->date()->toggleable(),
            TextEntry::make('created_at')->label('Dibuat')->dateTime()->toggleable(),
            TextEntry::make('updated_at')->label('Diubah')->dateTime()->toggleable(),
        ]);
    }
}
