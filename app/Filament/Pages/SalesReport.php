<?php

namespace App\Filament\Pages;

use App\Models\Sale;
use Filament\Pages\Page;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Carbon;
use UnitEnum;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;

class SalesReport extends Page implements HasSchemas, HasTable
{
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static string|UnitEnum|null $navigationGroup = 'Report & Audit';
    protected static ?string $navigationLabel = 'Sales Report';
    protected static ?string $title = 'Sales Report';

    protected string $view = 'filament.pages.sales-report';

    public ?array $filters = [
        'startDate' => null,
        'endDate'   => null,
        'search'    => null,
    ];
    

    public function mount(): void
    {
        $this->form->fill([
            'startDate' => now()->startOfMonth()->toDateString(),
            'endDate'   => now()->endOfMonth()->toDateString(),
            'search'    => null,
        ]);
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

    public function applyFilters(): void
    {
        // trigger re-render
    }

    public function exportExcel()
{
    $query = $this->table($this->makeTable())->getQuery();

    return \Maatwebsite\Excel\Facades\Excel::download(
        new \App\Exports\SalesReportExport($query),
        'sales-report.xlsx'
    );
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
                    ->when($search, function ($q, $search) {
                        $q->where(function ($sub) use ($search) {
                            $sub->where('id', 'like', "%{$search}%")
                                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('vehicle', fn ($v) =>
                                    $v->where('vin', 'like', "%{$search}%")
                                      ->orWhere('license_plate', 'like', "%{$search}%")
                                );
                        });
                    })
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
            ])
            ->paginated(false);
    }

    


    
}
