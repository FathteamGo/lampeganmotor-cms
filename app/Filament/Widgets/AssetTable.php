<?php

namespace App\Filament\Widgets;

use App\Models\OtherAsset;
use App\Models\Purchase;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class AssetTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Ringkasan Aset';

    public function table(Table $table): Table
    {
        // Hitung nilai asset (pastikan kolom-nya ada di DB)
        $hartaTidakBergerak = (float) OtherAsset::sum('value');
        $stokUnitBergerak   = (float) Purchase::sum('total_price');
        $piutangDiluar      = 0.0;
        $pencairanAdira     = 0.0;
        $avalist            = 0.0;
        $asetSaldo          = 0.0;
        $total = $hartaTidakBergerak + $stokUnitBergerak + $piutangDiluar + $pencairanAdira + $avalist + $asetSaldo;

        // Gunakan array dengan key unik (ID-like) — wajib untuk tracking Livewire
        $rows = [
            1 => ['asset' => 'HARTA TIDAK BERGERAK', 'nominal' => $hartaTidakBergerak],
            2 => ['asset' => 'STOCK UNIT BERGERAK', 'nominal' => $stokUnitBergerak],
            3 => ['asset' => 'PIUTANG DILUAR', 'nominal' => $piutangDiluar],
            4 => ['asset' => 'PENCAIRAN ADIRA FINANCE', 'nominal' => $pencairanAdira],
            5 => ['asset' => 'AVALIST DAN STOK UNIT TIDAK BERGERAK', 'nominal' => $avalist],
            6 => ['asset' => 'ASET BERUPA SALDO / UANG', 'nominal' => $asetSaldo],
            7 => ['asset' => 'Total Asset', 'nominal' => $total],
        ];

        return $table
            ->records(fn () => $rows) // <-- pakai records(), bukan query()/rows()
            ->columns([
                TextColumn::make('asset')
                    ->label('Asset')
                    ->wrap(),

                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->alignRight()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.')),
            ]);
    }
}
