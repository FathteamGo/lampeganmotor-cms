<?php
namespace App\Filament\Widgets;

use App\Models\Vehicle;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

class AvailableUnitsTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading          = 'Stok Unit Tidak bergerak';

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->query(
                Vehicle::query()
                    ->with(['vehicleModel.brand', 'type', 'year'])
                    ->where('status', 'available')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('vehicleModel.brand.name')->label('Brand')->searchable(),
                Tables\Columns\TextColumn::make('type.name')->label('Type')->searchable(),
                Tables\Columns\TextColumn::make('vehicleModel.name')->label('Model')->searchable(),
                Tables\Columns\TextColumn::make('year.year')->label('Tahun')->sortable(),
                Tables\Columns\TextColumn::make('license_plate')->label('Plat Nomor')->searchable(),
                Tables\Columns\TextColumn::make('purchase_price')->label('Harga Beli')->money('idr', true),
                Tables\Columns\TextColumn::make('sale_price')->label('Harga Jual')->money('idr', true),
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
