<?php
namespace App\Filament\Pages;

use App\Models\Purchase;
use App\Exports\PurchaseReportExport;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;


class PurchaseReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'Report & Audit';
    protected static ?string $navigationLabel                    = 'Purchase Report';
    protected static ?string $title                              = 'Purchase Report';

    protected string $view = 'filament.pages.purchase-report';

   public function table(Table $table): Table
    {
          // Tambah waktu eksekusi untuk export besar
        ini_set('max_execution_time', 300); // 5 menit

        return $table
            ->query(
                Purchase::query()
                    ->with([
                        'vehicle',
                        'supplier',
                        'vehicle.vehicleModel.brand',
                        'vehicle.type',
                        'vehicle.color',
                        'vehicle.year',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Invoice Number')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Date')
                    ->date('F d, Y'),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable(),

                Tables\Columns\TextColumn::make('supplier.address')
                    ->label('Address'),

                Tables\Columns\TextColumn::make('supplier.phone')
                    ->label('Phone'),

                Tables\Columns\TextColumn::make('vehicle.vehicleModel.brand.name')
                    ->label('Brand'),

                Tables\Columns\TextColumn::make('vehicle.type.name')
                    ->label('Type'),

                Tables\Columns\TextColumn::make('vehicle.vehicleModel.name')
                    ->label('Model'),

                Tables\Columns\TextColumn::make('vehicle.color.name')
                    ->label('Color'),

                Tables\Columns\TextColumn::make('vehicle.year.year')
                    ->label('Year'),

                Tables\Columns\TextColumn::make('total_price')
                    ->money('IDR')
                    ->label('Total Price'),
            ])
            ->filters([
                Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Start Date')
                            ->default(now()->startOfMonth()),

                        Forms\Components\DatePicker::make('until')
                            ->label('End Date')
                            ->default(now()->endOfMonth()),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn($q, $from) => $q->whereDate('purchase_date', '>=', $from))
                            ->when($data['until'] ?? null, fn($q, $until) => $q->whereDate('purchase_date', '<=', $until));
                    }),
                ])
            ->paginated(false);
           

//     ->headerActions([
//     Action::make('Export Excel')
//         ->label('Export Excel')
//         ->button()
//         ->icon('heroicon-o-download')
//         ->action(function () {
//             return Excel::download(new PurchaseReportExport, 'purchase-report.xlsx');
//         }),
// ]);
    }

    //  
}
