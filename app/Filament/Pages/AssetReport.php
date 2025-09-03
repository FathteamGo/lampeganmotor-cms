<?php

namespace App\Filament\Pages;

use App\Models\OtherAsset;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

class AssetReport extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'Report & Audit';
    protected static ?string $navigationLabel               = 'Asset Report';
    protected static ?string $title                         = 'Report & Audit
';

    protected string $view = 'filament.pages.asset-report';

    // ✅ table utama (OtherAsset)
    public function table(Table $table): Table
    {
        return $table
            ->query(OtherAsset::query())
            ->heading('Harta Tidak Bergerak')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama Asset')->searchable(),
                Tables\Columns\TextColumn::make('description')->label('Keterangan')->wrap(),
                Tables\Columns\TextColumn::make('acquisition_date')->label('Tanggal Perolehan')->date('d M Y'),
                Tables\Columns\TextColumn::make('value')->label('Nilai Asset')->money('idr', true),
            ])
            ->filters([
                Filter::make('acquisition_date')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('acquisition_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('acquisition_date', '<=', $date));
                    }),
            ]);
    }
}
