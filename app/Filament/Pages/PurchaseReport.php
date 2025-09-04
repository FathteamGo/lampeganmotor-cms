<?php

namespace App\Filament\Pages;

use App\Models\Purchase;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PurchaseReportExport;
use UnitEnum;

class PurchaseReport extends Page implements HasSchemas, Tables\Contracts\HasTable
{
    use InteractsWithSchemas;
    use Tables\Concerns\InteractsWithTable;

    protected static string|UnitEnum|null $navigationGroup = 'navigation.report_audit';
    protected static ?string $navigationLabel = 'navigation.purchase_report';
    protected static ?string $title = 'navigation.purchase_report_title';
    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.purchase-report';

    public ?array $filters = [
        'startDate' => null,
        'endDate'   => null,
        'search'    => null,
    ];

    // hanya role owner
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
        $this->filters['endDate']   = now()->endOfMonth()->toDateString();
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
                            ->placeholder('Search invoice / supplier / VIN...'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ])
            ->statePath('filters');
    }

    /** 
     * Query builder dengan filter
     */
    protected function buildQuery()
    {
        $start  = data_get($this->filters, 'startDate');
        $end    = data_get($this->filters, 'endDate');
        $search = data_get($this->filters, 'search');

        return Purchase::query()
            ->with([
                'vehicle.vehicleModel.brand',
                'vehicle.type',
                'vehicle.color',
                'vehicle.year',
                'supplier',
            ])
            ->when($start, fn ($q) => $q->whereDate('purchase_date', '>=', Carbon::parse($start)))
            ->when($end, fn ($q) => $q->whereDate('purchase_date', '<=', Carbon::parse($end)))
            ->when($search, fn ($q, $s) =>
                $q->where(function ($sub) use ($s) {
                    $sub->where('id', 'like', "%{$s}%")
                        ->orWhereHas('supplier', fn ($sup) => $sup->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('vehicle', fn ($v) =>
                            $v->where('vin', 'like', "%{$s}%")
                              ->orWhere('license_plate', 'like', "%{$s}%")
                        );
                })
            );
    }

    public function exportExcel()
    {
        return Excel::download(
            new PurchaseReportExport($this->buildQuery()),
            'purchase-report.xlsx'
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->buildQuery()) // selalu pakai filter terbaru
            ->columns([
                TextColumn::make('id')->label('Invoice Number')->sortable(),
                TextColumn::make('purchase_date')->label('Date')->date('F d, Y'),

                // Supplier
                TextColumn::make('supplier.name')->label('Supplier'),
                TextColumn::make('supplier.phone')->label('Phone'),
                TextColumn::make('supplier.address')->label('Address'),

                // Vehicle
                TextColumn::make('vehicle.vehicleModel.brand.name')->label('Brand'),
                TextColumn::make('vehicle.type.name')->label('Type'),
                TextColumn::make('vehicle.vehicleModel.name')->label('Model'),
                TextColumn::make('vehicle.color.name')->label('Color'),
                TextColumn::make('vehicle.year.year')->label('Year'),
                TextColumn::make('vehicle.vin')->label('VIN'),
                TextColumn::make('vehicle.license_plate')->label('License Plate'),
                TextColumn::make('vehicle.status')->label('Status'),

                // Purchase info
                TextColumn::make('total_price')->money('IDR')->label('Total Price'),
                TextColumn::make('payment_method')->label('Payment Method'),

                // Tambahan kolom manual
                TextColumn::make('otr')->label('OTR')->getStateUsing(fn () => ''),
                TextColumn::make('additional_fee')->label('Additional Fee')->getStateUsing(fn () => ''),
                TextColumn::make('dp')->label('DP')->getStateUsing(fn () => ''),
                TextColumn::make('remaining_debt')->label('Remaining Debt')->getStateUsing(fn () => ''),
                TextColumn::make('branch')->label('Branch')->getStateUsing(fn () => ''),
                TextColumn::make('notes')->label('Notes')->getStateUsing(fn () => ''),
            ])
            ->paginated(false);
    }

    public function applyFilters(): void
{
    // Tidak perlu isi apa pun
    // Livewire akan otomatis re-render tabel karena $filters berubah
}
}
