<?php
namespace App\Filament\Widgets;

use App\Models\Vehicle;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Forms\Components\DatePicker;

class AvailableUnitsTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    // Heading diambil dari lang file
    protected static ?string $heading = null;

    protected ?array $filters = [
        'from' => null,
        'until' => null,
    ];

    protected function getHeading(): ?string
    {
        return __('tables.available_units'); // key di lang file
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(
                Vehicle::query()
                    ->with(['vehicleModel.brand', 'type', 'year'])
                    ->where('status', 'available')
                    ->when($this->filters['from'], fn($q, $date) => $q->whereDate('purchase_date', '>=', $date))
                    ->when($this->filters['until'], fn($q, $date) => $q->whereDate('purchase_date', '<=', $date))
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label(__('tables.number'))->sortable(),
                Tables\Columns\TextColumn::make('vehicleModel.brand.name')->label(__('tables.brand'))->searchable(),
                Tables\Columns\TextColumn::make('type.name')->label(__('tables.type'))->searchable(),
                Tables\Columns\TextColumn::make('vehicleModel.name')->label(__('tables.model'))->searchable(),
                Tables\Columns\TextColumn::make('year.year')->label(__('tables.year'))->sortable(),
                Tables\Columns\TextColumn::make('license_plate')->label(__('tables.plate'))->searchable(),
                Tables\Columns\TextColumn::make('purchase_price')->label(__('tables.purchase_price'))->money('idr', true),
                Tables\Columns\TextColumn::make('sale_price')->label(__('tables.sale_price'))->money('idr', true),
            ])
            ->filters([
                Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        DatePicker::make('from')->label(__('tables.from')),
                        DatePicker::make('until')->label(__('tables.until')),
                    ])
                    ->query(fn($query, array $data) =>
                        $query
                            ->when($data['from'], fn($q, $date) => $q->whereDate('purchase_date', '>=', $date))
                            ->when($data['until'], fn($q, $date) => $q->whereDate('purchase_date', '<=', $date))
                    ),
            ]);
    }
}
