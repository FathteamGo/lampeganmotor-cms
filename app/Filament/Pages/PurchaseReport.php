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
            ->query(fn () => $this->buildQuery())
            ->columns([
                TextColumn::make('id')->label(__('tables.number'))->sortable(),
                TextColumn::make('purchase_date')->label(__('navigation.date'))->date('F d, Y'),

                // Supplier
                TextColumn::make('supplier.name')->label(__('navigation.supplier')),
                TextColumn::make('supplier.phone')->label(__('tables.phone')),
                TextColumn::make('supplier.address')->label(__('navigation.address')),

                // Vehicle
                TextColumn::make('vehicle.vehicleModel.brand.name')->label(__('tables.brand')),
                TextColumn::make('vehicle.type.name')->label(__('tables.type')),
                TextColumn::make('vehicle.vehicleModel.name')->label(__('tables.model')),
                TextColumn::make('vehicle.color.name')->label(__('tables.color')),
                TextColumn::make('vehicle.year.year')->label(__('tables.year')),
                TextColumn::make('vehicle.vin')->label(__('tables.vin')),
                TextColumn::make('vehicle.license_plate')->label(__('tables.license_plate')),
                TextColumn::make('vehicle.status')->label(__('tables.status')),

                // Purchase info
                TextColumn::make('total_price')->money('IDR')->label(__('tables.total_price')),
                TextColumn::make('payment_method')->label(__('tables.payment_method')),

                // Tambahan kolom manual
                TextColumn::make('otr')->label(__('tables.otr'))->getStateUsing(fn () => ''),
                TextColumn::make('additional_fee')->label(__('tables.additional_fee'))->getStateUsing(fn () => ''),
                TextColumn::make('dp')->label(__('tables.dp'))->getStateUsing(fn () => ''),
                TextColumn::make('remaining_debt')->label(__('tables.remaining_debt'))->getStateUsing(fn () => ''),
                TextColumn::make('branch')->label(__('tables.branch'))->getStateUsing(fn () => ''),
                TextColumn::make('notes')->label(__('tables.notes'))->getStateUsing(fn () => ''),
            ])
            ->paginated(false);
    }

    public function applyFilters(): void
    {
        // Livewire auto refresh
    }
}
