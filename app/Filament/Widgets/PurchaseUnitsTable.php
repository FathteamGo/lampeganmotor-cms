<?php
namespace App\Filament\Widgets;

use App\Models\Purchase;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

class PurchaseUnitsTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading          = 'Unit Bergerak';

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->query(
                Purchase::query()
                    ->with(['vehicle.vehicleModel', 'vehicle.year'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('vehicle.vehicleModel.name')->label('Nama Kendaraan'),
                Tables\Columns\TextColumn::make('vehicle.year.year')->label('Tahun'),
                Tables\Columns\TextColumn::make('notes')->label('Keterangan')->wrap(),
                Tables\Columns\TextColumn::make('purchase_date')->label('Tanggal')->date('d M Y'),
                Tables\Columns\TextColumn::make('total_price')->label('Nominal')->money('idr', true),
            ])
            ->filters([
                Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('purchase_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('purchase_date', '<=', $date));
                    }),
            ]);
    }
}
