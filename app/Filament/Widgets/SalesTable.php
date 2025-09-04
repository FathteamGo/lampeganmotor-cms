<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get as SchemaGet;

class SalesTable extends BaseWidget
{
    protected static ?string $heading = null;
    protected int|string|array $columnSpan = 'full';

    public ?string $dateStart = null;
    public ?string $dateEnd   = null;
    public ?string $search    = null;

    protected function getHeading(): ?string
    {
        return __('tables.sales');
    }

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
                        'customer',
                    ])
                    ->when($this->dateStart, fn ($q) => $q->whereDate('sale_date', '>=', $this->dateStart))
                    ->when($this->dateEnd,   fn ($q) => $q->whereDate('sale_date', '<=', $this->dateEnd))
                    ->when(filled($this->search), function ($q) {
                        $term = trim($this->search);
                        $s = '%' . $term . '%';
                        $num = preg_replace('/\D+/', '', $term) ?: null;

                        $q->where(function ($qq) use ($s, $num) {
                            $qq->where('notes', 'like', $s)
                               ->orWhere('sale_price', 'like', $s)
                               ->when($num, fn ($w) => $w->orWhere('id', (int) $num))
                               ->orWhereHas('customer', fn ($x) => $x->where('name', 'like', $s))
                               ->orWhereHas('vehicle.vehicleModel', fn ($x) => $x->where('name', 'like', $s))
                               ->orWhereHas('vehicle.vehicleModel.brand', fn ($x) => $x->where('name', 'like', $s));
                        });
                    })
                    ->latest('sale_date')
            )
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->filters([
                Filter::make('period')
                    ->label(__('tables.period'))
                    ->form([
                        Section::make()
                            ->columns([ 'default' => 2 ])
                            ->schema([
                                DatePicker::make('start')
                                    ->label(__('navigation.start_date'))
                                    ->displayFormat('d/m/Y')
                                    ->default(fn () => $this->dateStart ?? now()->startOfMonth()->toDateString())
                                    ->live()
                                    ->afterStateUpdated(function ($state, SchemaGet $get) {
                                        $this->dateStart = (string) $state;
                                        $this->dateEnd   = (string) ($get('end') ?? $this->dateEnd);
                                        $this->dispatch('pl-dates-updated', start: $this->dateStart, end: $this->dateEnd);
                                    }),
                                DatePicker::make('end')
                                    ->label(__('navigation.end_date'))
                                    ->displayFormat('d/m/Y')
                                    ->default(fn () => $this->dateEnd ?? now()->endOfMonth()->toDateString())
                                    ->minDate(fn (SchemaGet $get) => $get('start') ?: null)
                                    ->live()
                                    ->afterStateUpdated(function ($state, SchemaGet $get) {
                                        $this->dateStart = (string) ($get('start') ?? $this->dateStart);
                                        $this->dateEnd   = (string) $state;
                                        $this->dispatch('pl-dates-updated', start: $this->dateStart, end: $this->dateEnd);
                                    }),
                            ]),
                    ])
                    ->query(function ($query, array $data) {
                        $start = $data['start'] ?? null;
                        $end   = $data['end'] ?? null;
                        return $query
                            ->when($start, fn ($q) => $q->whereDate('sale_date', '>=', $start))
                            ->when($end,   fn ($q) => $q->whereDate('sale_date', '<=', $end));
                    })
                    ->indicateUsing(function (array $data): array {
                        $chips = [];
                        if (!empty($data['start'])) $chips[] = __('tables.from') . ': ' . $data['start'];
                        if (!empty($data['end']))   $chips[] = __('tables.until') . ': ' . $data['end'];
                        return $chips;
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('sale_date')->label(__('tables.date'))->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('customer.name')->label(__('tables.customer'))->placeholder('-'),
                Tables\Columns\TextColumn::make('vehicle.vehicleModel.brand.name')->label(__('tables.brand'))->badge()->placeholder('-')->toggleable(),
                Tables\Columns\TextColumn::make('vehicle.year.year')->label(__('tables.year'))->placeholder('-')->toggleable(),
                Tables\Columns\TextColumn::make('notes')->label(__('tables.notes'))->limit(40)->placeholder('-'),
                Tables\Columns\TextColumn::make('sale_price')->label(__('tables.amount'))->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('id')
                    ->label(__('tables.invoice_no'))
                    ->formatStateUsing(fn ($state) => 'INV' . str_pad((string) $state, 7, '0', STR_PAD_LEFT))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('vehicle.type.name')->label(__('tables.type'))->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('vehicle.vehicleModel.name')->label(__('tables.model'))->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('vehicle.color.name')->label(__('tables.color'))->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('payment_method')->label(__('tables.payment_method'))->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated(false)
            ->striped();
    }
}
