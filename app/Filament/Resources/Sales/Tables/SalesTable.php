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
                    ->color(fn($record) => $record->status === 'cancel' ? 'danger' : null)
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Nama')
                    ->color(fn($record) => $record->status === 'cancel' ? 'danger' : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('vehicle.vehicleModel.name')
                    ->label('Jenis Motor')
                    ->color(fn($record) => $record->status === 'cancel' ? 'danger' : null)
                    ->sortable(),

                // Harga Total Pembelian (ambil dari purchases.grand_total)
                TextColumn::make('total_pembelian')
                    ->label('H-Total Pembelian')
                    ->money('IDR', locale: 'id')
                    ->color(fn($record) => $record->status === 'cancel' ? 'danger' : null)
                    ->state(function ($record) {
                        // Coba ambil dari purchase.grand_total dulu
                        $purchase = \App\Models\Purchase::where('vehicle_id', $record->vehicle_id)->first();
                        $grandTotal = $purchase ? $purchase->grand_total : 0;

                        // Kalau grand_total = 0, fallback ke vehicle.purchase_price
                        if ($grandTotal == 0) {
                            return $record->vehicle?->purchase_price ?? 0;
                        }

                        return $grandTotal;
                    }),

                TextColumn::make('sale_price')
                    ->label('OTR')
                    ->money('IDR', locale: 'id')
                    ->color(fn($record) => $record->status === 'cancel' ? 'danger' : null),

                TextColumn::make('laba_kotor')
                    ->label('Laba Kotor')
                    ->money('IDR', locale: 'id')
                    ->color(fn($state, $record) => $record->status === 'cancel' ? 'danger' : ($state < 0 ? 'danger' : 'success'))
                    ->state(function ($record) {
                        if ($record->status === 'cancel') {
                            return 0;
                        }
                        return self::calculateLabaKotor($record);
                    }),

                // Kolom Laba Bersih dengan rumus yang benar
                TextColumn::make('laba_bersih')
                    ->label('Laba Bersih')
                    ->money('IDR', locale: 'id')
                    ->color(fn($state, $record) => $record->status === 'cancel' ? 'danger' : ($state < 0 ? 'danger' : 'success'))
                    ->state(function ($record) {
                        if ($record->status === 'cancel') {
                            return 0;
                        }
                        // Hitung Laba Kotor dulu
                        $labaKotor = self::calculateLabaKotor($record);

                        // Hitung Pengeluaran
                        $pengeluaran = ($record->cmo_fee ?? 0) + ($record->direct_commission ?? 0);

                        // Laba Bersih = Laba Kotor - Pengeluaran
                        return $labaKotor - $pengeluaran;
                    }),

                TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->color(fn($record) => $record->status === 'cancel' ? 'danger' : null)
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'cash' => 'Cash',
                            'credit' => 'Credit',
                            'tukartambah' => 'Tukar Tambah',
                            'cash_tempo' => 'Cash Tempo',
                            default => $state
                        };
                    })
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
                    ->color(fn($record) => $record->status === 'cancel' ? 'danger' : null)
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

                // Filter berdasarkan metode pembayaran
                Filter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->form([
                        Select::make('payment_method')
                            ->label('Metode')
                            ->options([
                                'cash' => 'Cash',
                                'credit' => 'Credit',
                                'tukartambah' => 'Tukar Tambah',
                                'cash_tempo' => 'Cash Tempo',
                            ])
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['payment_method'], fn($q) =>
                            $q->where('payment_method', $data['payment_method'])
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

    /**
     * Hitung Laba Kotor berdasarkan metode pembayaran
     *
     * RUMUS PER METODE PEMBAYARAN:
     *
     * CATATAN PENTING:
     * Harga Total Pembelian diambil dari purchases.grand_total
     * (sudah termasuk harga motor + semua biaya tambahan seperti STNK, pajak, service, dll)
     * Kalau data purchase tidak ada atau 0, pakai vehicle.purchase_price
     *
     * 1. CREDIT (Kredit via Leasing):
     *    Laba Kotor = OTR - DP PO - DP REAL - Harga Total Pembelian
     *    Catatan: Sisa pembayaran (OTR - DP PO - DP REAL) masuk ke leasing
     *
     * 2. CASH (Tunai):
     *    Laba Kotor = OTR - Harga Total Pembelian
     *
     * 3. CASH TEMPO (Tempo dari Customer):
     *    Laba Kotor = OTR - Harga Total Pembelian
     *    Catatan: Sisa pembayaran masuk tunggakan customer (dicatat sebagai aset)
     *
     * 4. TUKAR TAMBAH:
     *    Laba Kotor = OTR - Harga Total Pembelian
     *
     * LABA BERSIH = Laba Kotor - Pengeluaran (Fee CMO + Komisi Langsung)
     */
    private static function calculateLabaKotor($record): float
    {
        $otr = $record->sale_price ?? 0;
        $dpPo = $record->dp_po ?? 0;
        $dpReal = $record->dp_real ?? 0;

        // Ambil purchase berdasarkan vehicle_id
        $purchase = \App\Models\Purchase::where('vehicle_id', $record->vehicle_id)->first();

        // Gunakan grand_total attribute (total_price + additional costs)
        $hargaTotalPembelian = $purchase ? $purchase->grand_total : 0;

        // Fallback: kalau grand_total = 0, pakai vehicle.purchase_price
        if ($hargaTotalPembelian == 0) {
            $hargaTotalPembelian = $record->vehicle?->purchase_price ?? 0;
        }

        // Hitung laba kotor berdasarkan metode pembayaran
        return match($record->payment_method) {
            // Credit: OTR - DP PO - DP REAL - Harga Total Pembelian
            'credit' => $otr - $dpPo + $dpReal - $hargaTotalPembelian,

            // Cash: OTR - Harga Total Pembelian
            'cash' => $otr - $hargaTotalPembelian,

            // Cash Tempo: OTR - Harga Total Pembelian
            // Sisa pembayaran masuk ke tunggakan customer (aset)
            'cash_tempo' => $otr - $hargaTotalPembelian,

            // Tukar Tambah: OTR - Harga Total Pembelian
            'tukartambah' => $otr - $hargaTotalPembelian,

            // Default untuk metode lain
            default => $otr - $hargaTotalPembelian
        };
    }
}
