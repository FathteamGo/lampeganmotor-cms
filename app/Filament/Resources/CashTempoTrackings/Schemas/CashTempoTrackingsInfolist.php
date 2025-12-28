<?php

namespace App\Filament\Resources\CashTempoTrackings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class CashTempoTrackingsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            
            // INFO CUSTOMER
            Section::make('Informasi Customer')
                ->schema([
                    TextEntry::make('customer.name')
                        ->label('Nama Customer'),
                    
                    TextEntry::make('customer.phone')
                        ->label('No. Telepon'),
                    
                    TextEntry::make('customer.address')
                        ->label('Alamat')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            // INFO KENDARAAN
            Section::make('Informasi Kendaraan')
                ->schema([
                    TextEntry::make('vehicle.vehicleModel.name')
                        ->label('Jenis Motor'),
                    
                    TextEntry::make('vehicle.type.name')
                        ->label('Tipe'),
                    
                    TextEntry::make('vehicle.year.year')
                        ->label('Tahun'),
                    
                    TextEntry::make('vehicle.color.name')
                        ->label('Warna'),
                    
                    TextEntry::make('vehicle.license_plate')
                        ->label('No Polisi'),
                    
                    TextEntry::make('vehicle.purchase_price')
                        ->label('Harga Pembelian')
                        ->money('IDR', locale: 'id'),
                ])
                ->columns(2),

            // DETAIL PEMBAYARAN CASH TEMPO
            Section::make('Detail Pembayaran Cash Tempo')
                ->description('Rumus: Sisa Pembayaran = OTR - (DP PO + DP REAL)')
                ->schema([
                    TextEntry::make('sale_date')
                        ->label('Tanggal Penjualan')
                        ->date('d M Y'),
                    
                    TextEntry::make('sale_price')
                        ->label('OTR (Harga Jual)')
                        ->money('IDR', locale: 'id'),
                    
                    TextEntry::make('dp_po')
                        ->label('DP PO')
                        ->money('IDR', locale: 'id'),
                    
                    TextEntry::make('dp_real')
                        ->label('DP REAL')
                        ->money('IDR', locale: 'id'),
                    
                    TextEntry::make('remaining_payment')
                        ->label('Sisa Pembayaran (Uang Mengendap)')
                        ->money('IDR', locale: 'id')
                        ->color('warning')
                        ->size('lg')
                        ->weight(FontWeight::Bold),
                    
                    TextEntry::make('due_date')
                        ->label('Tanggal Jatuh Tempo')
                        ->date('d M Y')
                        ->color(fn($record) => 
                            $record->due_date && $record->due_date <= now() 
                                ? 'danger' 
                                : ($record->due_date && $record->due_date <= now()->addDays(7) 
                                    ? 'warning' 
                                    : 'success')
                        )
                        ->icon(fn($record) => 
                            $record->due_date && $record->due_date <= now() 
                                ? 'heroicon-o-exclamation-triangle' 
                                : 'heroicon-o-calendar'
                        ),
                    
                    TextEntry::make('hari_tersisa')
                        ->label('Status Jatuh Tempo')
                        ->badge()
                        ->state(function ($record) {
                            if (!$record->due_date) return 'Belum ditentukan';
                            
                            // ✅ FIX: Pakai startOfDay() & cast ke integer
                            $now = now()->startOfDay();
                            $dueDate = $record->due_date->startOfDay();
                            $days = (int) $now->diffInDays($dueDate, false);
                            
                            if ($days < 0) {
                                return 'TERLAMBAT ' . abs($days) . ' hari';
                            } elseif ($days == 0) {
                                return 'JATUH TEMPO HARI INI';
                            } else {
                                return 'Tersisa ' . $days . ' hari lagi';
                            }
                        })
                        ->color(fn($record) => 
                            !$record->due_date ? 'gray' : (
                                now()->startOfDay()->diffInDays($record->due_date->startOfDay(), false) < 0 
                                    ? 'danger' 
                                    : (now()->startOfDay()->diffInDays($record->due_date->startOfDay(), false) <= 7 
                                        ? 'warning' 
                                        : 'success')
                            )
                        ),
                ])
                ->columns(2),

            // LABA BERSIH
            Section::make('Perhitungan Laba')
                ->description('Rumus Cash Tempo: Laba = OTR - Harga Pembelian - Fee CMO - Komisi Langsung')
                ->schema([
                    TextEntry::make('laba_kotor')
                        ->label('Laba Kotor')
                        ->money('IDR', locale: 'id')
                        ->state(function ($record) {
                            $otr = (float) ($record->sale_price ?? 0);
                            $hBeli = (float) optional($record->vehicle)->purchase_price;
                            return $otr - $hBeli;
                        }),
                    
                    TextEntry::make('cmo_fee')
                        ->label('Fee CMO')
                        ->money('IDR', locale: 'id'),
                    
                    TextEntry::make('direct_commission')
                        ->label('Komisi Langsung')
                        ->money('IDR', locale: 'id'),
                    
                    TextEntry::make('laba_bersih')
                        ->label('Laba Bersih')
                        ->money('IDR', locale: 'id')
                        ->state(function ($record) {
                            $otr = (float) ($record->sale_price ?? 0);
                            $hBeli = (float) optional($record->vehicle)->purchase_price;
                            $cmo = (float) ($record->cmo_fee ?? 0);
                            $komisi = (float) ($record->direct_commission ?? 0);
                            
                            // Rumus Cash Tempo: OTR - Harga Pembelian - CMO - Komisi
                            $laba = $otr - $hBeli - $cmo - $komisi;
                            
                            return max($laba, 0);
                        })
                        ->color(fn($state) => $state >= 0 ? 'success' : 'danger')
                        ->weight(FontWeight::Bold),
                ])
                ->columns(2),

            // INFO SALES & LAINNYA
            Section::make('Informasi Tambahan')
                ->schema([
                    TextEntry::make('user.name')
                        ->label('Sales / Executive'),
                    
                    TextEntry::make('cmo')
                        ->label('CMO / Mediator'),
                    
                    TextEntry::make('order_source')
                        ->label('Sumber Order')
                        ->badge(),
                    
                    TextEntry::make('branch_name')
                        ->label('Cabang'),
                    
                    TextEntry::make('result')
                        ->label('Hasil')
                        ->badge(),
                    
                    TextEntry::make('status')
                        ->label('Status Penjualan')
                        ->badge()
                        ->color(fn($state) => match($state) {
                            'proses' => 'info',
                            'kirim' => 'success',
                            'selesai' => 'warning',
                            default => 'gray',
                        }),
                ])
                ->columns(2),

            // CATATAN
            Section::make('Catatan')
                ->schema([
                    TextEntry::make('notes')
                        ->label('Catatan')
                        ->columnSpanFull()
                        ->placeholder('Tidak ada catatan'),
                ])
                ->collapsed()
                ->collapsible(),

            // TIMESTAMP
            Section::make('Informasi Sistem')
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Dibuat Pada')
                        ->dateTime('d M Y, H:i'),
                    
                    TextEntry::make('updated_at')
                        ->label('Terakhir Diubah')
                        ->dateTime('d M Y, H:i'),
                ])
                ->columns(2)
                ->collapsed()
                ->collapsible(),
        ]);
    }
}
