<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Forms\Components\DatePicker;

class TunggakanTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    // Heading null, akan override di getHeading()
    protected static ?string $heading = null;

    protected ?array $filters = [
        'from' => null,
        'until' => null,
    ];

    protected function getHeading(): ?string
    {
        return __('tables.outstanding_payments'); 
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(
                Sale::query()
                    ->with(['vehicle.vehicleModel', 'vehicle.year'])
                    ->whereNotIn('payment_method', ['cash', 'credit']) // hanya tunggakan
                    ->when($this->filters['from'], fn($q, $date) => $q->whereDate('sale_date', '>=', $date))
                    ->when($this->filters['until'], fn($q, $date) => $q->whereDate('sale_date', '<=', $date))
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('tables.number'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.vehicleModel.name')
                    ->label(__('tables.name')),
                Tables\Columns\TextColumn::make('vehicle.year.year')
                    ->label(__('tables.year')),
                Tables\Columns\TextColumn::make('sale_date')
                    ->label(__('tables.date'))
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('sale_price')
                    ->label(__('tables.amount'))
                    ->money('idr', true),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('tables.payment_method')),
            ])
            ->filters([
                Tables\Filters\Filter::make('sale_date')
                    ->form([
                        DatePicker::make('from')->label(__('tables.from')),
                        DatePicker::make('until')->label(__('tables.until')),
                    ])
                    ->query(fn($query, array $data) => 
                        $query
                            ->when($data['from'], fn($q, $date) => $q->whereDate('sale_date', '>=', $date))
                            ->when($data['until'], fn($q, $date) => $q->whereDate('sale_date', '<=', $date))
                    ),
            ]);
    }
}
