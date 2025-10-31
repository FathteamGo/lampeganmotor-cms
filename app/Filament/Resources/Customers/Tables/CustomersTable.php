<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('No. Telepon')
                    ->searchable(),

                TextColumn::make('address')
                    ->label('Alamat')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('nik')
                    ->label('NIK')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),

                // ===== KOLOM BARU: JUMLAH PEMBELIAN (Hanya status != cancel) =====
                TextColumn::make('total_units')
                    ->label('Total Unit Dibeli')
                    ->getStateUsing(fn($record) => 
                        $record->sales()
                            ->where('status', '!=', 'cancel')
                            ->count()
                    )
                    ->badge()
                    ->color('success'),

                // ===== PEMBELIAN TERAKHIR (Hanya status != cancel) =====
                TextColumn::make('last_purchase')
                    ->label('Pembelian Terakhir')
                    ->getStateUsing(function ($record) {
                        $lastSale = $record->sales()
                            ->where('status', '!=', 'cancel')
                            ->latest('sale_date')
                            ->first();

                        return $lastSale
                            ? $lastSale->sale_date->format('d M Y')
                            : '-';
                    })
                    ->sortable(),

                TextColumn::make('instagram')
                    ->label('Instagram')
                    ->formatStateUsing(fn($record) => $record->instagram ? '@' . $record->instagram : '-')
                    ->url(fn($record) => $record->instagram_url, shouldOpenInNewTab: true)
                    ->toggleable(),

                TextColumn::make('tiktok')
                    ->label('TikTok')
                    ->formatStateUsing(fn($record) => $record->tiktok ? '@' . $record->tiktok : '-')
                    ->url(fn($record) => $record->tiktok_url, shouldOpenInNewTab: true)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            // ===== FILTERS =====
            ->filters([
                SelectFilter::make('period')
                    ->label('Periode Pembelian')
                    ->options([
                        'this_month' => 'Bulan Ini',
                        'last_month' => 'Bulan Kemarin',
                        'all'        => 'Semua',
                    ])
                    ->default('this_month')
                    ->query(function (Builder $query, array $data) {
                        if (!isset($data['value']) || $data['value'] === 'all') {
                            return $query;
                        }

                        return $query->whereHas('sales', function (Builder $q) use ($data) {
                            $q->where('status', '!=', 'cancel');

                            if ($data['value'] === 'this_month') {
                                $q->whereBetween('sale_date', [
                                    now()->startOfMonth(),
                                    now()->endOfMonth()
                                ]);
                            } elseif ($data['value'] === 'last_month') {
                                $q->whereBetween('sale_date', [
                                    now()->subMonth()->startOfMonth(),
                                    now()->subMonth()->endOfMonth()
                                ]);
                            }
                        });
                    }),

                SelectFilter::make('month')
                    ->label('Bulan')
                    ->options([
                        '1'  => 'Januari',
                        '2'  => 'Februari',
                        '3'  => 'Maret',
                        '4'  => 'April',
                        '5'  => 'Mei',
                        '6'  => 'Juni',
                        '7'  => 'Juli',
                        '8'  => 'Agustus',
                        '9'  => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            return $query->whereHas('sales', function (Builder $q) use ($data) {
                                $q->where('status', '!=', 'cancel')
                                  ->whereMonth('sale_date', $data['value']);
                            });
                        }
                        return $query;
                    }),

                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(function () {
                        $currentYear = now()->year;
                        $years = [];
                        for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                            $years[$i] = $i;
                        }
                        return $years;
                    })
                    ->default(now()->year)
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            return $query->whereHas('sales', function (Builder $q) use ($data) {
                                $q->where('status', '!=', 'cancel')
                                  ->whereYear('sale_date', $data['value']);
                            });
                        }
                        return $query;
                    }),
            ])

            ->defaultSort('created_at', 'desc')

            ->recordActions([
                EditAction::make()->label('Edit'),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus'),
                ]),
            ]);
    }
}
