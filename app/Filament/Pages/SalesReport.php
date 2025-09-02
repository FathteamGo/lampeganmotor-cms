<?php
namespace App\Filament\Pages;

use App\Models\Sale;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

use UnitEnum;

class SalesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|UnitEnum|null $navigationGroup = 'Report & Audit';
    protected static ?string $navigationLabel                  = 'Sales Report';
    protected static ?string $title                            = 'Sales Report';

    protected string $view = 'filament.pages.sales-report';

    public ?string $from = null;
    public ?string $until = null;

    public function mount(): void
    {
        $this->from  = now()->startOfMonth()->toDateString();
        $this->until = now()->endOfMonth()->toDateString();
    }

    public function applyFilters(): void
    {
        // Tidak perlu isi apa-apa, cukup untuk trigger re-render
    }
    

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => 
                Sale::query()
                    ->with(['vehicle.vehicleModel.brand', 'vehicle.type', 'vehicle.color', 'vehicle.year', 'customer'])
                    ->when($this->from, fn($q) => $q->whereDate('sale_date', '>=', Carbon::parse($this->from)))
                    ->when($this->until, fn($q) => $q->whereDate('sale_date', '<=', Carbon::parse($this->until)))
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Invoice Number')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('sale_date')->label('Date')->date('F d, Y'),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer')->searchable(),
                Tables\Columns\TextColumn::make('vehicle.vehicleModel.brand.name')->label('Brand'),
                Tables\Columns\TextColumn::make('vehicle.type.name')->label('Type'),
                Tables\Columns\TextColumn::make('vehicle.vehicleModel.name')->label('Model'),
                Tables\Columns\TextColumn::make('vehicle.color.name')->label('Color'),
                Tables\Columns\TextColumn::make('vehicle.year.year')->label('Year'),
                Tables\Columns\TextColumn::make('vehicle.vin')->label('VIN'),
                Tables\Columns\TextColumn::make('vehicle.license_plate')->label('License Plate'),
                Tables\Columns\TextColumn::make('sale_price')->money('IDR')->label('Sale Price'),
                Tables\Columns\TextColumn::make('payment_method')->label('Payment Method'),
            ])
            ->filters([])
            ->paginated(false);
    }

}
