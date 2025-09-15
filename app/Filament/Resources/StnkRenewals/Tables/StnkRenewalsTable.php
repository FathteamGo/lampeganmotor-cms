<?php

namespace App\Filament\Resources\StnkRenewals\Tables;


use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;


class StnkRenewalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tgl')->label('Tanggal')->date(),
                TextColumn::make('license_plate')->label('Nomor Polisi')->searchable(),
                TextColumn::make('atas_nama_stnk')->label('Atas Nama'),
                TextColumn::make('customer.name')->label('Customer'),
                TextColumn::make('customer.phone')->label('Nomor Telepon'),
                TextColumn::make('total_pajak_jasa')->label('Total Pajak + Jasa')->money('idr', true),
                TextColumn::make('dp')->label('DP')->money('idr', true),
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
            ])
            ->recordActions([
                ViewAction::make()->label('Lihat'),
                EditAction::make()->label('Ubah'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus'),
                ]),
            ]);
    }
}
