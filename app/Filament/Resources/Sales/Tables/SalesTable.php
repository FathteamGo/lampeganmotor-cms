<?php

namespace App\Filament\Resources\Sales\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.address')->label('Alamat')->toggleable(),
                TextColumn::make('customer.phone')->label('No Telepon')->toggleable(),

                TextColumn::make('vehicle.vehicleModel.name')->label('Jenis Motor')->searchable()->sortable(),
                TextColumn::make('vehicle.type.name')->label('Type')->toggleable(),
                TextColumn::make('vehicle.year.year')->label('Tahun')->sortable()->toggleable(),
                TextColumn::make('vehicle.color.name')->label('Warna')->toggleable(),
                TextColumn::make('vehicle.license_plate')->label('No Pol')->searchable()->sortable(),

                TextColumn::make('vehicle.purchase_price')
                    ->label('H-Total Pembelian')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('sale_price')
                    ->label('OTR')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('dp_po')
                    ->label('Dp Po')
                    ->money('IDR', locale: 'id')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('dp_real')
                    ->label('Dp Real')
                    ->money('IDR', locale: 'id')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('pencairan')
                    ->label('Pencairan')
                    ->money('IDR', locale: 'id')
                    ->placeholder('-')
                    ->toggleable(),

                // ✅ Laba Bersih dihitung ulang
                TextColumn::make('laba_bersih')
                    ->label('Laba Bersih')
                    ->money('IDR', locale: 'id')
                    ->placeholder('-')
                    ->state(fn($record) => max(
                        ($record->pencairan ?? $record->sale_price ?? 0)
                        + ($record->dp_real ?? 0)
                        - ($record->vehicle?->purchase_price ?? 0)
                        - ($record->cmo_fee ?? 0)
                        - ($record->direct_commission ?? 0),
                        0
                    ))
                    ->toggleable(),

                TextColumn::make('payment_method')->label('Metode Pembayaran')->searchable()->toggleable(),
                TextColumn::make('cmo')->label('CMO')->toggleable(),
                TextColumn::make('cmo_fee')->label('FEE CMO')->money('IDR', locale: 'id')->toggleable(),
                TextColumn::make('direct_commission')->label('Komisi Langsung')->money('IDR', locale: 'id')->toggleable(),

                TextColumn::make('order_source')->label('Sumber Order')->searchable()->toggleable(),
                TextColumn::make('user.name')->label('Ex')->searchable()->sortable()->toggleable(),
                TextColumn::make('branch_name')->label('Cabang')->searchable()->toggleable(),
                TextColumn::make('result')->label('Hasil')->searchable()->toggleable(),
                TextColumn::make('status')->label('Status')->searchable()->toggleable(),

                TextColumn::make('sale_date')->label('Tanggal')->date()->sortable(),
                TextColumn::make('notes')->label('Note')->limit(40)->toggleable(),

                TextColumn::make('created_at')->dateTime()->label(__('tables.created_at'))->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->label(__('tables.updated_at'))->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])

            // 🗓️ Filter gabungan Bulan & Tahun
            ->filters([
                Filter::make('periode')
                    ->label('Periode')
                    ->form([
                        Select::make('month')
                            ->label('Bulan')
                            ->options([
                                '1' => 'Januari',
                                '2' => 'Februari',
                                '3' => 'Maret',
                                '4' => 'April',
                                '5' => 'Mei',
                                '6' => 'Juni',
                                '7' => 'Juli',
                                '8' => 'Agustus',
                                '9' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ])
                            ->default(date('n')),

                        Select::make('year')
                            ->label('Tahun')
                            ->options(function () {
                                $years = range(date('Y'), 2020);
                                return collect($years)->mapWithKeys(fn($year) => [$year => $year])->toArray();
                            })
                            ->default(date('Y')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['month'] ?? null, fn($q, $month) => $q->whereMonth('sale_date', $month))
                            ->when($data['year'] ?? null, fn($q, $year) => $q->whereYear('sale_date', $year));
                    }),
            ])

            ->recordActions([
                ViewAction::make()->label(__('tables.view')),
                EditAction::make()->label(__('tables.edit')),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label(__('tables.delete')),
                ]),
            ]);
    }
}
