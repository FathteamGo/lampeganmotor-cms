<?php

namespace App\Filament\Pages;

use App\Models\Purchase;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PurchaseReportExport;
use UnitEnum;

class PurchaseReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | UnitEnum | null $navigationGroup = 'navigation.report_audit';
    protected static ?string $navigationLabel = 'navigation.purchase_report';
    protected static ?string $title = 'navigation.purchase_report_title';
    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.purchase-report';

    // Filter form data
    public ?array $filters = [
        'startDate' => null,
        'endDate'   => null,
        'search'    => null,
    ];

    public function mount(): void
    {
        $this->filters = [
            'startDate' => now()->startOfMonth()->toDateString(),
            'endDate'   => now()->endOfMonth()->toDateString(),
            'search'    => null,
        ];
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

    public function form(): array
    {
        return [
            DatePicker::make('startDate')
                ->label('Start Date')
                ->maxDate(fn () => $this->filters['endDate'] ?: now()),

            DatePicker::make('endDate')
                ->label('End Date')
                ->minDate(fn () => $this->filters['startDate'] ?: now())
                ->maxDate(now()),

            TextInput::make('search')
                ->label('Search')
                ->placeholder('Search invoice / supplier / VIN...'),
        ];
    }

    public function exportExcel()
    {
        $query = $this->table($this->makeTable())->getQuery();

        return Excel::download(new PurchaseReportExport($query), 'purchase-report.xlsx');
    }

    public function table(Table $table): Table
    {
        $start  = $this->filters['startDate'] ?? null;
        $end    = $this->filters['endDate'] ?? null;
        $search = $this->filters['search'] ?? null;

        return $table
            ->query(
                Purchase::query()
                    ->with([
                        'vehicle.vehicleModel.brand',
                        'vehicle.type',
                        'vehicle.color',
                        'vehicle.year',
                        'supplier',
                    ])
                    ->when($start, fn ($q) => $q->whereDate('purchase_date', '>=', Carbon::parse($start)))
                    ->when($end, fn ($q) => $q->whereDate('purchase_date', '<=', Carbon::parse($end)))
                    ->when($search, fn ($q) =>
                        $q->where('id', 'like', "%{$search}%")
                          ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"))
                          ->orWhereHas('vehicle', fn ($v) =>
                              $v->where('vin', 'like', "%{$search}%")
                                ->orWhere('license_plate', 'like', "%{$search}%")
                          )
                    )
            )
            ->columns([
                TextColumn::make('id')->label(__('navigation.invoice_number'))->sortable(),
                TextColumn::make('purchase_date')->label(__('navigation.date'))->date('F d, Y'),

                // Supplier Info
                TextColumn::make('supplier.name')->label(__('navigation.supplier')),
                TextColumn::make('supplier.address')->label(__('navigation.address')),
                TextColumn::make('supplier.phone')->label(__('navigation.phone')),

                // Vehicle Info
                TextColumn::make('vehicle.vehicleModel.brand.name')->label(__('navigation.brand')),
                TextColumn::make('vehicle.type.name')->label(__('navigation.type')),
                TextColumn::make('vehicle.vehicleModel.name')->label(__('navigation.model')),
                TextColumn::make('vehicle.color.name')->label(__('navigation.color')),
                TextColumn::make('vehicle.year.year')->label(__('navigation.year')),
                TextColumn::make('vehicle.vin')->label(__('navigation.vin')),
                TextColumn::make('vehicle.license_plate')->label(__('navigation.license_plate')),
                TextColumn::make('vehicle.status')->label(__('navigation.status')),

                // Purchase Info
                TextColumn::make('total_price')->money('IDR')->label(__('navigation.total_price')),
            ])
            ->filters([
                Filter::make('purchase_date')
                    ->form([
                        DatePicker::make('from')->label(__('navigation.start_date'))->default(now()->startOfMonth()),
                        DatePicker::make('until')->label(__('navigation.end_date'))->default(now()->endOfMonth()),
                    ])
                    ->query(fn ($query, array $data) =>
                        $query
                            ->when($data['from'] ?? null, fn($q, $from) => $q->whereDate('purchase_date', '>=', $from))
                            ->when($data['until'] ?? null, fn($q, $until) => $q->whereDate('purchase_date', '<=', $until))
                    ),
            ])
            ->paginated(false);
    }
}
