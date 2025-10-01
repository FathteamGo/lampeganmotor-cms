<?php

namespace App\Filament\Pages;

use App\Models\Sale;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use UnitEnum;

class SalesReport extends Page implements HasSchemas, Tables\Contracts\HasTable
{
    use InteractsWithSchemas;
    use Tables\Concerns\InteractsWithTable;

    protected static string|UnitEnum|null $navigationGroup = 'navigation.report_audit';
    protected static ?string $navigationLabel = 'navigation.sales_report';
    protected static ?string $title = 'navigation.sales_report_title';
    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.sales-report';

    public ?array $filters = [
        'startDate' => null,
        'endDate'   => null,
        'search'    => null,
    ];

    // Role only owner
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'owner';
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'owner';
    }

    public static function getNavigationGroup(): ?string
    {
        return __(static::$navigationGroup);
    }

    public static function getNavigationLabel(): string
    {
        return __(static::$navigationLabel);
    }

    public function getTitle(): string
    {
        return __(static::$title);
    }

    public function mount(): void
    {
        $this->filters['startDate'] = now()->startOfMonth()->toDateString();
        $this->filters['endDate'] = now()->endOfMonth()->toDateString();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label(__('navigation.start_date'))
                            ->maxDate(fn (Get $get) => $get('endDate') ?: now()),

                        DatePicker::make('endDate')
                            ->label(__('navigation.end_date'))
                            ->minDate(fn (Get $get) => $get('startDate') ?: now())
                            ->maxDate(now()),

                        TextInput::make('search')
                            ->label(__('navigation.search'))
                            ->placeholder(__('navigation.search')),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ])
            ->statePath('filters');
    }

    public function exportExcel()
    {
        $query = $this->table($this->makeTable())->getQuery();

        return Excel::download(new SalesReportExport($query), 'sales-report.xlsx');
    }

    public function table(Table $table): Table
    {
        $start  = data_get($this->filters, 'startDate');
        $end    = data_get($this->filters, 'endDate');
        $search = data_get($this->filters, 'search');

        return $table
            ->query(
                Sale::query()
                    ->with(['vehicle', 'vehicle.vehicleModel.brand', 'vehicle.type', 'vehicle.color', 'vehicle.year', 'customer', 'user'])
                    ->when($start, fn ($q) => $q->whereDate('sale_date', '>=', Carbon::parse($start)))
                    ->when($end, fn ($q) => $q->whereDate('sale_date', '<=', Carbon::parse($end)))
                    ->when($search, fn ($q, $s) =>
                        $q->where(function ($sub) use ($s) {
                            $sub->where('id', 'like', "%{$s}%")
                                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$s}%"))
                                ->orWhereHas('vehicle', fn ($v) =>
                                    $v->where('vin', 'like', "%{$s}%")
                                      ->orWhere('license_plate', 'like', "%{$s}%")
                                );
                        })
                    )
            )
            ->columns([
                TextColumn::make('id')->label(__('tables.number'))->sortable(),

                TextColumn::make('sale_date')->label(__('navigation.date'))->date('F d, Y'),

                // Customer
                TextColumn::make('customer.name')->label(__('navigation.customer')),
                TextColumn::make('customer.phone')->label(__('tables.phone')),
                TextColumn::make('customer.address')->label(__('tables.location')),

                // Vehicle
                TextColumn::make('vehicle.vehicleModel.brand.name')->label(__('tables.brand')),
                TextColumn::make('vehicle.type.name')->label(__('tables.type')),
                TextColumn::make('vehicle.vehicleModel.name')->label(__('tables.model')),
                TextColumn::make('vehicle.color.name')->label(__('tables.color')),
                TextColumn::make('vehicle.year.year')->label(__('tables.year')),
                TextColumn::make('vehicle.vin')->label(__('tables.vin')),
                TextColumn::make('vehicle.license_plate')->label(__('tables.license_plate')),

                // Financial columns
                TextColumn::make('vehicle.purchase_price')->label('H TOTAL PEMBELIAN')
                    ->getStateUsing(fn($record) => $record->vehicle->purchase_price ?? 0)
                    ->money('IDR'),

                TextColumn::make('vehicle.sale_price')->label('OTR')
                    ->getStateUsing(fn($record) => $record->vehicle->sale_price ?? 0)
                    ->money('IDR'),

                TextColumn::make('dp_po_calc')->label('DP PO')
                    ->getStateUsing(fn ($record) => optional($record->vehicle)->dp_percentage
                        ? round(($record->vehicle->sale_price ?? $record->sale_price ?? 0) * ($record->vehicle->dp_percentage / 100))
                        : 0
                    )
                    ->money('IDR'),

                TextColumn::make('dp_real_calc')->label('DP REAL')
                    ->getStateUsing(fn ($record) => $record->payment_method === 'cash_tempo'
                        ? max(0, ($record->sale_price ?? 0) - ($record->remaining_payment ?? 0))
                        : 0
                    )
                    ->money('IDR'),

                TextColumn::make('pencairan')->label('PENCAIRAN')
                    ->getStateUsing(fn ($record) => $record->pencairan ?? 0)
                    ->money('IDR'),

                TextColumn::make('sale_price')->label('TOTAL PENJUALAN')
                    ->getStateUsing(fn ($record) => $record->sale_price ?? 0)
                    ->money('IDR'),

                TextColumn::make('laba_bersih')->label('LABA BERSIH')
                    ->getStateUsing(fn ($record) => ($record->sale_price ?? 0) - ($record->vehicle->purchase_price ?? 0))
                    ->money('IDR'),

                TextColumn::make('payment_method')->label('Metode Pembayaran')
                    ->formatStateUsing(fn ($s) => match ($s) {
                        'cash' => 'Cash',
                        'credit' => 'Credit',
                        'tukartambah' => 'Tukar Tambah',
                        'cash_tempo' => 'Cash Tempo',
                        default => $s ?: '-',
                    }),

                TextColumn::make('remaining_payment')->label('Sisa Pembayaran')
                    ->getStateUsing(fn ($record) => $record->remaining_payment ?? 0)
                    ->money('IDR'),

                TextColumn::make('due_date')->label('Jatuh Tempo')->date(),

                TextColumn::make('cmo')->label('CMO / Mediator')->formatStateUsing(fn ($s) => $s ?: '-'),
                TextColumn::make('cmo_fee')->label('Fee CMO')->getStateUsing(fn ($r) => $r->cmo_fee ?? 0)->money('IDR'),
                TextColumn::make('komisi_langsung')->label('Komisi Langsung')->getStateUsing(fn ($r) => $r->komisi_langsung ?? 0)->money('IDR'),

                TextColumn::make('user.name')->label('Ex')->formatStateUsing(fn ($s) => $s ?: '-'),
                TextColumn::make('branch_name')->label('Cabang')->formatStateUsing(fn ($s) => $s ?: '-'),
                TextColumn::make('result')->label('Hasil')->formatStateUsing(fn ($s) => $s ?: '-'),

                TextColumn::make('order_source')->label('Sumber Order')
                    ->formatStateUsing(fn ($s) => match ($s) {
                        'fb' => 'Facebook',
                        'ig' => 'Instagram',
                        'tiktok' => 'TikTok',
                        'walk_in' => 'Walk In',
                        default => $s ?: '-',
                    }),

                TextColumn::make('status')->label('Status')
                    ->formatStateUsing(fn ($s) => match ($s) {
                        'proses' => 'Proses',
                        'kirim' => 'Kirim',
                        'selesai' => 'Selesai',
                        default => $s ?: '-',
                    }),

                TextColumn::make('notes')->label('Catatan')->formatStateUsing(fn ($s) => $s ?: '-'),
            ])
            ->paginated(false);
    }

    public function applyFilters(): void
    {
        // Livewire auto refresh
    }
}
