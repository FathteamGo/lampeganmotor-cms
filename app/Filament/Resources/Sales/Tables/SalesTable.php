<?php

namespace App\Filament\Resources\Sales\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;

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

                TextColumn::make('vehicle.vehicleModel.name')
                    ->label('Jenis Motor')
                    ->sortable(),

                TextColumn::make('vehicle.purchase_price')
                    ->label('H-Total Pembelian')
                    ->money('IDR', locale: 'id'),

                TextColumn::make('sale_price')
                    ->label('OTR')
                    ->money('IDR', locale: 'id'),

                TextColumn::make('laba_bersih')
                    ->label('Laba Bersih')
                    ->money('IDR', locale: 'id')
                    ->color(function ($record) {
                        $otr       = $record->sale_price ?? 0;
                        $dpPo      = $record->dp_po ?? 0;
                        $dpReal    = $record->dp_real ?? 0;
                        $pencairan = $record->pencairan ?? 0;
                        $hBeli     = $record->vehicle?->purchase_price ?? 0;

                        $laba = 0;
                        switch ($record->payment_method) {
                            case 'cash':
                                $laba = $otr - $hBeli;
                                break;
                            case 'credit':
                                $laba = $otr - $dpPo - $dpReal - $hBeli;
                                break;
                            case 'cash_tempo':
                                $laba = $otr - $hBeli;
                                break;
                            case 'dana_tunai':
                                $laba = $otr - $dpPo - $pencairan;
                                break;
                        }

                        // Laba Bersih = Laba - Pengeluaran
                        $labaBersih = $laba - ($record->cmo_fee ?? 0) - ($record->direct_commission ?? 0);

                        return $labaBersih < 0 ? 'danger' : 'success';
                    })
                    ->state(function ($record) {
                        $otr       = $record->sale_price ?? 0;
                        $dpPo      = $record->dp_po ?? 0;
                        $dpReal    = $record->dp_real ?? 0;
                        $pencairan = $record->pencairan ?? 0;
                        $hBeli     = $record->vehicle?->purchase_price ?? 0;

                        $laba = 0;
                        switch ($record->payment_method) {
                            case 'cash':
                                $laba = $otr - $hBeli;
                                break;
                            case 'credit':
                                $laba = $otr - $dpPo - $dpReal - $hBeli;
                                break;
                            case 'cash_tempo':
                                $laba = $otr - $hBeli;
                                break;
                            case 'dana_tunai':
                                $laba = $otr - $dpPo - $pencairan;
                                break;
                        }

                        // Laba Bersih = Laba - Pengeluaran (tidak pakai max, biar bisa minus)
                        return $laba - ($record->cmo_fee ?? 0) - ($record->direct_commission ?? 0);
                    }),

                TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => fn($state, $record) =>
                            ($record->vehicle?->status === 'sold'),
                        'success' => fn($state, $record) =>
                            in_array($state, ['kirim', 'selesai'])
                            && $record->vehicle?->status !== 'sold',
                        'info' => fn($state) => in_array($state, ['proses']),
                        'danger' => fn($state) => in_array($state, ['cancel']),
                    ])
                    ->sortable(),

                TextColumn::make('sale_date')
                    ->label('Tanggal')
                    ->date(),
            ])

            ->filters([
                Filter::make('Bulan')
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
                            $q->whereMonth('sale_date', $data['month'])
                        );
                    }),

                Filter::make('Tahun')
                    ->form([
                        Select::make('year')
                            ->label('Tahun')
                            ->options(
                                DB::table('sales')
                                    ->selectRaw('YEAR(sale_date) as year')
                                    ->distinct()
                                    ->orderBy('year', 'desc')
                                    ->pluck('year', 'year')
                            )
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['year'], fn($q) =>
                            $q->whereYear('sale_date', $data['year'])
                        );
                    }),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('invoice_cash')
                    ->label('Invoice (Cash)')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn($record) => $record->payment_method === 'cash')
                    ->url(fn($record) => route('sales.invoice.cash', $record))
                    ->openUrlInNewTab(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}