<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;

class InfoListStnkRenewals
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
                TextColumn::make('tgl')->label('Tanggal')->date(),
                TextColumn::make('license_plate')->label('Nomor Polisi')->searchable(),
                TextColumn::make('atas_nama_stnk')->label('Atas Nama'),
                TextColumn::make('customer.name')->label('Customer'),
                TextColumn::make('customer.name')->label('Customer'),
                TextColumn::make('vendor')->label('Nama Vendor'),
                // TextColumn::make('payvendor')->label('Pembayaran ke Vendor'),
                TextColumn::make('customer.phone')->label('Nomor Telepon'),
                TextColumn::make('total_pajak_jasa')->label('Total Pajak + Jasa')->money('idr', true),
                TextColumn::make('dp')->label('DP / Dibayar')->money('idr', true),
                TextColumn::make('sisa_pembayaran')->label('Sisa Pembayaran')->money('idr', true),
                TextColumn::make('margin_total')->label('Margin')->money('idr', true),
                TextColumn::make('diambil_tgl')->label('Tanggal Diambil')->date(),
                BadgeColumn::make('status')
                ->label('Status')
                ->colors([
                    'warning' => 'pending',
                    'primary' => 'progress',
                    'success' => 'done',
                ])
                ->formatStateUsing(fn ($state) => match ($state) {
                    'pending' => 'Pending',
                    'progress' => 'Progress',
                    'done' => 'Done',
                    default => $state,
                })
        ]);
    }
}
