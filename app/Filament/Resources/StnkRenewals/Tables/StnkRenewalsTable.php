<?php

namespace App\Filament\Resources\StnkRenewals\Tables;

use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;

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
                TextColumn::make('jenis_pekerjaan')->label('Jenis Pekerjaan'),
                TextColumn::make('vendor')->label('Vendor'),
                TextColumn::make('payvendor')->label('Bayar ke Vendor')->money('idr', true),
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
                    }),
            ])
            ->filters([
                // Filter Tanggal
                Tables\Filters\Filter::make('Tanggal')
                    ->form([
                        DatePicker::make('date')
                            ->label('Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['date'], fn($q) =>
                            $q->whereDate('tgl', $data['date'])
                        );
                    }),

                // Filter Bulan
                Tables\Filters\Filter::make('Bulan')
                    ->form([
                        Select::make('month')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                            ])
                            ->default(now()->month),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['month'], fn($q) =>
                            $q->whereMonth('tgl', $data['month'])
                        );
                    }),

                // Filter Tahun
                Tables\Filters\Filter::make('Tahun')
                    ->form([
                        Select::make('year')
                            ->label('Tahun')
                            ->options(
                                DB::table('stnk_renewals')
                                    ->selectRaw('YEAR(tgl) as year')
                                    ->distinct()
                                    ->orderBy('year', 'desc')
                                    ->pluck('year', 'year')
                            )
                            ->default(now()->year),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['year'], fn($q) =>
                            $q->whereYear('tgl', $data['year'])
                        );
                    }),
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
