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

            TextEntry::make('vehicle.vehicleModel.brand.name')
                ->label('Merek'),

            TextEntry::make('vehicle.vehicleModel.name')
                ->label('Model'),

            TextEntry::make('supplier.name')
                ->label('Supplier'),

            TextEntry::make('purchase_date')
                ->date('d F Y')
                ->label('Tanggal Pembelian'),

            TextEntry::make('notes')
                ->label('Catatan Pembelian')
                ->default('-')
                ->columnSpanFull(),

            /* 🔹 DATA MOTOR */
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
            
            TextEntry::make('vehicle.type.name')
                ->label('Tipe'),
            
            TextEntry::make('vehicle.color.name')
                ->label('Warna'),
            
            TextEntry::make('vehicle.year.year')
                ->label('Tahun'),
            
            TextEntry::make('vehicle.odometer')
                ->label('Odometer')
                ->formatStateUsing(fn ($state) =>
                    $state ? number_format((float) $state, 0, ',', '.') . ' km' : '-'
                ),

            TextEntry::make('vehicle.sale_price')
                ->label('Harga Jual')
                ->formatStateUsing(fn ($state) =>
                    $state ? 'Rp ' . number_format((float) $state, 0, ',', '.') : '-'
                ),

            TextEntry::make('vehicle.down_payment')
                ->label('DP')
                ->formatStateUsing(fn ($state) =>
                    $state ? 'Rp ' . number_format((float) $state, 0, ',', '.') : '-'
                ),
            
            TextEntry::make('vehicle.engine_specification')
                ->label('Spesifikasi Mesin')
                ->default('-'),
            
            TextEntry::make('vehicle.location')
                ->label('Lokasi')
                ->default('-'),
            
            TextEntry::make('vehicle.notes')
                ->label('Catatan Kendaraan')
                ->columnSpanFull()
                ->default('-'),

            /* 🔹 HARGA MOTOR (TANPA BIAYA TAMBAHAN) */
            TextEntry::make('vehicle.purchase_price')
                ->label('Harga Beli Motor')
                ->formatStateUsing(fn ($state) =>
                    'Rp ' . number_format((float) $state, 0, ',', '.')
                )
                ->weight('semibold')
                ->color('primary')
                ->size('lg')
                ->columnSpanFull(),

            /* 🔹 BIAYA TAMBAHAN (DETAIL) */
            RepeatableEntry::make('additionalCosts')
                ->label('Detail Biaya Tambahan')
                ->schema([
                    TextEntry::make('category.name')
                        ->label('Kategori')
                        ->default('Lain-lain'),
                    TextEntry::make('price')
                        ->label('Harga')
                        ->formatStateUsing(fn ($state) =>
                            'Rp ' . number_format((float) $state, 0, ',', '.')
                        ),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn ($record) => $record->additionalCosts->count() > 0),

            /* 🔹 TOTAL BIAYA TAMBAHAN */
            TextEntry::make('total_biaya_tambahan')
                ->label('Total Biaya Tambahan')
                ->state(fn ($record) =>
                    $record->additionalCosts->sum('price')
                )
                ->formatStateUsing(fn ($state) =>
                    'Rp ' . number_format((float) $state, 0, ',', '.')
                )
                ->weight('semibold')
                ->color('warning')
                ->columnSpanFull()
                ->visible(fn ($record) => $record->additionalCosts->count() > 0),

            /* 🔹 HARGA TOTAL PEMBELIAN (MOTOR + BIAYA TAMBAHAN) */
            TextEntry::make('total_price')
                ->label('Harga Total Pembelian')
                ->formatStateUsing(fn ($state) =>
                    'Rp ' . number_format((float) $state, 0, ',', '.')
                )
                ->weight('bold')
                ->size('xl')
                ->color('success')
                ->columnSpanFull(),
        ]);
    }
}