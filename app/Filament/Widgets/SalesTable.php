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
    protected static ?string $heading = 'Sales';

    public ?string $dateStart = null;
    public ?string $dateEnd   = null;
    public ?string $search    = null;     // <-- NEW

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
                        'customer',
                    ])
                    ->when($this->dateStart, fn ($q) => $q->whereDate('sale_date', '>=', $this->dateStart))
                    ->when($this->dateEnd,   fn ($q) => $q->whereDate('sale_date', '<=', $this->dateEnd))

                    // === GLOBAL SEARCH dari parent ===
                    ->when(filled($this->search), function ($q) {
                        $term = trim($this->search);
                        $s = '%' . $term . '%';
                        $num = preg_replace('/\D+/', '', $term) ?: null;

                        $q->where(function ($qq) use ($s, $num) {
                            $qq->where('notes', 'like', $s)
                               ->orWhere('sale_price', 'like', $s)
                               // cari berdasarkan ID murni (buat "INV0000123" user ketik angka saja juga bisa)
                               ->when($num, fn ($w) => $w->orWhere('id', (int) $num))

                               ->orWhereHas('customer', fn ($x) => $x->where('name', 'like', $s))
                               ->orWhereHas('vehicle.vehicleModel', fn ($x) => $x->where('name', 'like', $s))
                               ->orWhereHas('vehicle.vehicleModel.brand', fn ($x) => $x->where('name', 'like', $s));
                        });
                    })
                    ->latest('sale_date')
            )

            // === Filter tanggal di atas tabel (punyamu) ===
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->filters([
                Filter::make('period')
                    ->label('Periode')
                    ->form([
                        Section::make()
                            ->columns([
                                'default' => 2, 'sm' => 2, 'md' => 2, 'lg' => 2, 'xl' => 2, '2xl' => 2,
                            ])
                            ->extraAttributes(['class' => 'gap-2'])
                            ->schema([
                                DatePicker::make('start')
                                    ->label('Tanggal Awal')
                                    ->displayFormat('d/m/Y')
                                    ->default(fn () => $this->dateStart ?? now()->startOfMonth()->toDateString())
                                    ->live()
                                    ->afterStateUpdated(function ($state, SchemaGet $get) {
                                        $this->dateStart = (string) $state;
                                        $this->dateEnd   = (string) ($get('end') ?? $this->dateEnd);
                                        $this->dispatch('pl-dates-updated', start: $this->dateStart, end: $this->dateEnd);
                                    })
                                    ->columnSpan(1),

                                DatePicker::make('end')
                                    ->label('Tanggal Akhir')
                                    ->displayFormat('d/m/Y')
                                    ->default(fn () => $this->dateEnd ?? now()->endOfMonth()->toDateString())
                                    ->minDate(fn (SchemaGet $get) => $get('start') ?: null)
                                    ->live()
                                    ->afterStateUpdated(function ($state, SchemaGet $get) {
                                        $this->dateStart = (string) ($get('start') ?? $this->dateStart);
                                        $this->dateEnd   = (string) $state;
                                        $this->dispatch('pl-dates-updated', start: $this->dateStart, end: $this->dateEnd);
                                    })
                                    ->columnSpan(1),
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
                        if (!empty($data['start'])) $chips[] = 'Dari: '.$data['start'];
                        if (!empty($data['end']))   $chips[] = 'Sampai: '.$data['end'];
                        return $chips;
                    }),
            ])

            ->columns([
                Tables\Columns\TextColumn::make('sale_date')->label('#')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('customer.name')->label('Nama')->placeholder('-'),
                Tables\Columns\TextColumn::make('vehicle.vehicleModel.brand.name')->label('Kategori')->badge()->placeholder('-')->toggleable(),
                Tables\Columns\TextColumn::make('vehicle.year.year')->label('Tahun')->placeholder('-')->toggleable(),
                Tables\Columns\TextColumn::make('notes')->label('Keterangan')->limit(40)->placeholder('-'),
                Tables\Columns\TextColumn::make('sale_price')->label('Nominal')->money('IDR')->sortable(),

                Tables\Columns\TextColumn::make('id')
                    ->label('No Invoice')
                    ->formatStateUsing(fn ($state) => 'INV' . str_pad((string) $state, 7, '0', STR_PAD_LEFT))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('vehicle.type.name')->label('Tipe')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('vehicle.vehicleModel.name')->label('Model')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('vehicle.color.name')->label('Warna')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('payment_method')->label('Metode')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated(false)
            ->striped();
    }
}
