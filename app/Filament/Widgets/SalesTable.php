<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SalesTable extends BaseWidget
{
    protected static ?string $heading = 'Sales';

    public ?string $dateStart = null;
    public ?string $dateEnd   = null;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sale::query()
                    ->with([
                        'vehicle.vehicleModel.brand',
                        'vehicle.type',
                        'vehicle.color',
                        'vehicle.year',
                        'customer', // kalau ada relasi customer
                    ])
                    ->when($this->dateStart, fn ($q) => $q->whereDate('sale_date', '>=', $this->dateStart))
                    ->when($this->dateEnd,   fn ($q) => $q->whereDate('sale_date', '<=', $this->dateEnd))
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No Invoice')
                    ->formatStateUsing(fn ($state) => 'INV'.str_pad($state, 7, '0', STR_PAD_LEFT))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('sale_date')
                    ->label('Tanggal')
                    ->date('j F')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('vehicle.vehicleModel.brand.name')
                    ->label('Merk')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('vehicle.type.name')
                    ->label('Tipe')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('vehicle.vehicleModel.name')
                    ->label('Model')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('vehicle.color.name')
                    ->label('Warna')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('vehicle.year.year')
                    ->label('Tahun')
                    ->toggleable(),

                // Kolom-kolom finansial yang ada:
                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Total Penjualan')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode')
                    ->toggleable(),

                // Catatan (kalau ada)
                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated(false);
    }
}
