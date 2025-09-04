<?php
namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

class TunggakanTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Tunggakan';

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->query(
                Sale::query()
                    ->with(['vehicle.vehicleModel', 'vehicle.year'])
                    ->whereNotIn('payment_method', ['cash', 'credit']) // ✅ perbaikan disini
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('vehicle.vehicleModel.name')->label('Nama Kendaraan'),
                Tables\Columns\TextColumn::make('vehicle.year.year')->label('Tahun'),
                Tables\Columns\TextColumn::make('sale_date')->label('Tanggal')->date('d M Y'),
                Tables\Columns\TextColumn::make('sale_price')->label('Nominal')->money('idr', true), // ✅ di Sale field harga itu `sale_price`, bukan `amount`
                Tables\Columns\TextColumn::make('payment_method')->label('Metode Pembayaran'),
            ]);
    }
}
