<?php

namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PurchaseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            /** =========================
             * DATA PEMBELIAN
             * ========================= */
            TextEntry::make('data_pembelian_header')
                ->label('DATA PEMBELIAN')
                ->state('')
                ->weight('bold')
                ->size('lg')
                ->columnSpanFull(),

            TextEntry::make('supplier.name')
                ->label('Supplier'),

            TextEntry::make('purchase_date')
                ->label('Tanggal Pembelian')
                ->date('d F Y'),

            TextEntry::make('notes')
                ->label('Catatan Pembelian')
                ->default('-')
                ->columnSpanFull(),

            /** =========================
             * DATA KENDARAAN
             * ========================= */
            TextEntry::make('data_kendaraan_header')
                ->label('DATA KENDARAAN')
                ->state('')
                ->weight('bold')
                ->size('lg')
                ->columnSpanFull(),

            TextEntry::make('vehicle.vehicleModel.brand.name')
                ->label('Merek'),

            TextEntry::make('vehicle.vehicleModel.name')
                ->label('Model'),

            TextEntry::make('vehicle.type.name')
                ->label('Tipe'),

            TextEntry::make('vehicle.color.name')
                ->label('Warna'),

            TextEntry::make('vehicle.year.year')
                ->label('Tahun'),

            TextEntry::make('vehicle.vin')
                ->label('Nomor Rangka'),

            TextEntry::make('vehicle.engine_number')
                ->label('Nomor Mesin'),

            TextEntry::make('vehicle.license_plate')
                ->label('Plat Nomor')
                ->default('-'),

            TextEntry::make('vehicle.bpkb_number')
                ->label('Nomor BPKB')
                ->default('-'),

            TextEntry::make('vehicle.odometer')
                ->label('Odometer')
                ->formatStateUsing(fn ($state) =>
                    $state ? number_format($state, 0, ',', '.') . ' km' : '-'
                ),

            TextEntry::make('vehicle.sale_price')
                ->label('Harga Jual')
                ->formatStateUsing(fn ($state) =>
                    $state ? 'Rp ' . number_format($state, 0, ',', '.') : '-'
                ),

            TextEntry::make('vehicle.down_payment')
                ->label('DP')
                ->formatStateUsing(fn ($state) =>
                    $state ? 'Rp ' . number_format($state, 0, ',', '.') : '-'
                ),

            TextEntry::make('vehicle.engine_specification')
                ->label('Spesifikasi Mesin')
                ->default('-'),

            TextEntry::make('vehicle.location')
                ->label('Lokasi')
                ->default('-'),

            TextEntry::make('vehicle.notes')
                ->label('Catatan Kendaraan')
                ->default('-')
                ->columnSpanFull(),

            /** =========================
             * DATA BIAYA
             * ========================= */
            TextEntry::make('data_biaya_header')
                ->label('DATA BIAYA PEMBELIAN')
                ->state('')
                ->weight('bold')
                ->size('lg')
                ->columnSpanFull(),

            TextEntry::make('vehicle.purchase_price')
                ->label('Harga Beli Motor')
                ->formatStateUsing(fn ($state) =>
                    'Rp ' . number_format($state, 0, ',', '.')
                )
                ->weight('bold')
                ->color('primary')
                ->columnSpanFull(),

            RepeatableEntry::make('additionalCosts')
                ->label('Rincian Biaya Tambahan')
                ->schema([
                    TextEntry::make('category.name')
                        ->label('Kategori')
                        ->default('Lain-lain'),

                    TextEntry::make('price')
                        ->label('Harga')
                        ->formatStateUsing(fn ($state) =>
                            'Rp ' . number_format($state, 0, ',', '.')
                        ),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn ($record) => $record->additionalCosts->count() > 0),

            TextEntry::make('total_biaya_tambahan')
                ->label('Total Biaya Tambahan')
                ->state(fn ($record) =>
                    $record->additionalCosts->sum('price')
                )
                ->formatStateUsing(fn ($state) =>
                    'Rp ' . number_format($state, 0, ',', '.')
                )
                ->weight('semibold')
                ->color('warning')
                ->columnSpanFull()
                ->visible(fn ($record) => $record->additionalCosts->count() > 0),

            TextEntry::make('total_purchase')
            ->label('TOTAL PEMBELIAN')
            ->state(function ($record) {
                return number_format(
                    ($record->vehicle->purchase_price ?? 0)
                    + $record->additionalCosts->sum('price'),
                    0,
                    ',',
                    '.'
                );
            })
            ->prefix('Rp ')
            ->weight('bold')
            ->color('success')
            ->extraAttributes([
                'class' => 'text-green-600 font-bold text-xl',
            ])
        ]);
    }
}
