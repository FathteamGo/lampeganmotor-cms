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
                            ->label('Start Date')
                            ->maxDate(fn (Get $get) => $get('endDate') ?: now()),

                        DatePicker::make('endDate')
                            ->label('End Date')
                            ->minDate(fn (Get $get) => $get('startDate') ?: now())
                            ->maxDate(now()),

                        TextInput::make('search')
                            ->label('Search')
                            ->placeholder('Search invoice / customer / VIN...'),
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
                ->with(['vehicle.vehicleModel.brand', 'vehicle.type', 'vehicle.color', 'vehicle.year', 'customer'])
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
            TextColumn::make('id')->label('Invoice Number')->sortable(),
            TextColumn::make('sale_date')->label('Date')->date('F d, Y'),
            TextColumn::make('customer.name')->label('Customer'),
            TextColumn::make('customer.phone')->label('Phone'),
            TextColumn::make('customer.address')->label('Location'),
            TextColumn::make('vehicle.vehicleModel.brand.name')->label('Brand'),
            TextColumn::make('vehicle.type.name')->label('Type'),
            TextColumn::make('vehicle.vehicleModel.name')->label('Model'),
            TextColumn::make('vehicle.color.name')->label('Color'),
            TextColumn::make('vehicle.year.year')->label('Year'),
            TextColumn::make('vehicle.vin')->label('VIN'),
            TextColumn::make('vehicle.license_plate')->label('License Plate'),
            TextColumn::make('sale_price')->money('IDR')->label('Sale Price'),
            TextColumn::make('payment_method')->label('Payment Method'),

            // Tambahan kolom kosong
            TextColumn::make('total_price')->label('Total Price')->getStateUsing(fn() => ''),
            TextColumn::make('otr')->label('OTR')->getStateUsing(fn() => ''),
            TextColumn::make('dp_po')->label('DP PO')->getStateUsing(fn() => ''),
            TextColumn::make('dp_real')->label('DP Real')->getStateUsing(fn() => ''),
            TextColumn::make('piutang')->label('Piutang')->getStateUsing(fn() => ''),
            TextColumn::make('total_penjualan')->label('Total Penjualan')->getStateUsing(fn() => ''),
            TextColumn::make('laba_bersih')->label('Net Profit')->getStateUsing(fn() => ''),
            TextColumn::make('ket')->label('Ket')->getStateUsing(fn() => ''),
            TextColumn::make('cmo')->label('CMO')->getStateUsing(fn() => ''),
            TextColumn::make('fee_cmo')->label('Fee CMO')->getStateUsing(fn() => ''),
            TextColumn::make('sumber_order')->label('Order Source')->getStateUsing(fn() => ''),
            TextColumn::make('ex')->label('Ex')->getStateUsing(fn() => ''),
            TextColumn::make('cabang')->label('Branch')->getStateUsing(fn() => ''),
        ])
        ->paginated(false);
}

public function applyFilters(): void
{
    // Tidak perlu isi apa-apa,
    // karena filter otomatis dipakai oleh query().
}
}
