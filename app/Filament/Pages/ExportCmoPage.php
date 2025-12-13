<?php

namespace App\Filament\Pages;

use App\Exports\CmoIncomeExport;
use App\Models\Sale;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class CmoIncomeReport extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected string $view = 'filament.pages.export-cmo-page';


    public function exportExcel()
    {
        $cmo = Auth::user();

        return Excel::download(
            new CmoIncomeExport($cmo->id),
            'penghasilan-cmo-' . strtolower($cmo->name) . '.xlsx'
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sale::query()
                    ->where('user_id', Auth::id())
                    ->with(['customer', 'vehicle'])
            )
            ->columns([
                TextColumn::make('sale_date')->label('Tanggal'),
                TextColumn::make('customer.name')->label('Customer'),
                TextColumn::make('vehicle.model')->label('Unit'),
                TextColumn::make('vehicle.nopol')->label('Nopol'),
                TextColumn::make('sale_price')->label('Harga Jual')->money('IDR'),
                TextColumn::make('cmo_fee')->label('Fee')->money('IDR'),
            ])
            ->paginated(false);
    }
}

