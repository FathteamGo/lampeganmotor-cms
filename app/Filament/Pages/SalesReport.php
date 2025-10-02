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
                    ->with(['vehicle.vehicleModel.brand', 'vehicle.type', 'vehicle.color', 'vehicle.year', 'customer', 'user'])
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
                TextColumn::make('sale_date')->label(__('navigation.date'))->date('F d, Y')->sortable(),
                TextColumn::make('customer.name')->label(__('navigation.customer'))->searchable(),
                TextColumn::make('customer.phone')->label(__('tables.phone')),
                TextColumn::make('customer.address')->label(__('tables.location')),
                TextColumn::make('vehicle.vehicleModel.brand.name')->label(__('tables.brand')),
                TextColumn::make('vehicle.type.name')->label(__('tables.type')),
                TextColumn::make('vehicle.vehicleModel.name')->label(__('tables.model')),
                TextColumn::make('vehicle.color.name')->label(__('tables.color')),
                TextColumn::make('vehicle.year.year')->label(__('tables.year')),
                TextColumn::make('vehicle.vin')->label(__('tables.vin')),
                TextColumn::make('vehicle.license_plate')->label(__('tables.license_plate')),
                
                // Financial columns
                TextColumn::make('vehicle.purchase_price')
                    ->label(__('tables.purchase_price'))
                    ->money('IDR'),
                
                TextColumn::make('sale_price')
                    ->label(__('tables.sale_price'))
                    ->money('IDR'),
                
                TextColumn::make('total_price')
                    ->label(__('tables.total_price'))
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->sale_price ?? 0),
                
                TextColumn::make('otr')
                    ->label(__('tables.otr'))
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->sale_price ?? 0),
                
               TextColumn::make('payment_method')->label(__('tables.payment_method')),
                
                TextColumn::make('dp_po')
                    ->label(__('tables.dp_po'))
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->dp_po ?? 0),
                
                TextColumn::make('dp_real')
                    ->label(__('tables.dp_real'))
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->dp_real ?? 0),
                
                TextColumn::make('remaining_payment')
                    ->label('Sisa Pembayaran')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->remaining_payment ?? 0),
                
                TextColumn::make('due_date')
                    ->label('Tanggal Jatuh Tempo')
                    ->date('F d, Y')
                    ->placeholder('-'),
                
                TextColumn::make('pencairan')
                    ->label('Pencairan')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => match($record->payment_method) {
                        'cash', 'tukartambah' => $record->sale_price ?? 0,
                        'credit', 'cash_tempo' => ($record->dp_real ?? 0) + ($record->remaining_payment ?? 0),
                        default => $record->sale_price ?? 0
                    }),
                
                TextColumn::make('piutang')
                    ->label('Piutang')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => ($record->sale_price ?? 0) - ($record->dp_real ?? 0)),
                
                TextColumn::make('total_penjualan')
                    ->label(__('tables.total_penjualan'))
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->sale_price ?? 0),
                
                TextColumn::make('laba_bersih')
                    ->label('Laba Bersih')
                    ->money('IDR', locale: 'id')
                    ->state(fn($record) => max(
                        // Pencairan sudah mencakup dp_real + sisa pembayaran (kalau kredit/cash_tempo)
                        ($record->pencairan ?? $record->sale_price ?? 0)
                        - ($record->vehicle?->purchase_price ?? 0)
                        - ($record->cmo_fee ?? 0)
                        - ($record->direct_commission ?? 0),
                        0
                    )),

                
                // CMO & Commission
                TextColumn::make('cmo')
                    ->label('CMO')
                    ->getStateUsing(fn ($record) => $record->cmo ?? '-'),
                
                TextColumn::make('cmo_fee')
                    ->label('Fee CMO')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->cmo_fee ?? 0),
                
                TextColumn::make('direct_commission')
                    ->label('Komisi Langsung')
                    ->money('IDR')
                    ->getStateUsing(fn ($record) => $record->direct_commission ?? 0),
                
                // Additional Information
                TextColumn::make('order_source')
                    ->label('Sumber Order')
                    ->formatStateUsing(fn ($s) => match ($s) {
                        'fb' => 'Facebook',
                        'ig' => 'Instagram',
                        'tiktok' => 'TikTok',
                        'walk_in' => 'Walk In',
                        default => $s ?: '-',
                    }),
                
                TextColumn::make('user.name')
                    ->label('Ex')
                    ->getStateUsing(fn ($record) => $record->user?->name ?? '-'),
                
                TextColumn::make('branch_name')
                    ->label('Cabang')
                    ->getStateUsing(fn ($record) => $record->branch_name ?? '-'),
                
                TextColumn::make('result')
                    ->label('Hasil')
                    ->getStateUsing(fn ($record) => $record->result ?? '-'),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($s) => match ($s) {
                        'proses' => 'Proses',
                        'kirim' => 'Kirim',
                        'selesai' => 'Selesai',
                        default => $s ?: '-',
                    }),
                
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(50)
                    ->getStateUsing(fn ($record) => $record->notes ?? '-'),
            ])
            ->paginated(false);
    }

    public function applyFilters(): void
    {
        // Filter otomatis dipakai oleh query(), jadi kosong
    }
}
