<?php
namespace App\Filament\Widgets;

use App\Models\Purchase;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

class PurchaseUnitsTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = null; // biarkan null

    protected function getHeading(): ?string
    {
        return __('tables.moving_units');
    }


    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->query(
                Purchase::query()
                    ->with(['vehicle.vehicleModel', 'vehicle.year'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label(__('tables.number'))->sortable(),
                Tables\Columns\TextColumn::make('vehicle.vehicleModel.name')->label(__('tables.name')),
                Tables\Columns\TextColumn::make('vehicle.year.year')->label(__('tables.year')),
                Tables\Columns\TextColumn::make('notes')->label(__('tables.notes'))->wrap(),
                Tables\Columns\TextColumn::make('purchase_date')->label(__('navigation.date'))->date('d M Y'),
                Tables\Columns\TextColumn::make('total_price')->label(__('tables.amount'))->money('idr', true),
            ])
            ->filters([
                Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label(__('tables.from')),
                        \Filament\Forms\Components\DatePicker::make('until')->label(__('tables.until')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('purchase_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('purchase_date', '<=', $date));
                    }),
            ]);
    }
}
