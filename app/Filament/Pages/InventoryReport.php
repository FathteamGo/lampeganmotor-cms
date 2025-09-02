<?php
namespace App\Filament\Pages;

use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Columns\Layout\Grid;



class InventoryReport extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'Report & Audit';
    protected static ?int $navigationSort                        = 3;
    protected static ?string $navigationLabel                    = 'Inventory Report';
    protected static ?string $title                              = 'Laporan & Audit Stok';

    // protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected string $view = 'filament.pages.inventory-report';


    public function table(Table $table): Table

    {
        return $table
            ->query(
                Vehicle::query()->with(['vehicleModel.brand', 'type', 'year', 'color', 'photos', 'additionalCosts'])
            )
           ->columns([
            Split::make([
    // Foto kiri
    ImageColumn::make('photo_thumb')
        ->getStateUsing(function ($record) {
            $path = $record->photos->first()?->path;
            return $path ? Storage::disk('public')->url($path) : null;
        })
        ->width(85)
        ->height(90)
        ->extraAttributes([
            'style' => 'border-radius:4px; object-fit:cover; background:#e5e7eb;',
        ]),

    // Detail kanan
    Grid::make(2)->schema([
        // Judul di atas (full width, span 2 kolom)
        TextColumn::make('displayName')
            ->label('')
            ->weight('bold')
            ->extraAttributes([
                'style' => 'font-size:14px; font-weight:600; grid-column: span 2; margin-bottom:6px;',
            ]),

        // Kolom kiri
        Tables\Columns\TextColumn::make('vehicleModel.brand.name')
            ->formatStateUsing(fn($state) => "Brand : {$state}"),
        Tables\Columns\TextColumn::make('vehicleModel.name')
            ->formatStateUsing(fn($state) => "Model : {$state}"),

        Tables\Columns\TextColumn::make('type.name')
            ->formatStateUsing(fn($state) => "Type : {$state}"),
        Tables\Columns\TextColumn::make('color.name')
            ->formatStateUsing(fn($state) => "Color : {$state}"),

        Tables\Columns\TextColumn::make('year.year')
            ->formatStateUsing(fn($state) => "Year : {$state}"),
        Tables\Columns\TextColumn::make('license_plate')
            ->formatStateUsing(fn($state) => "License Plate : {$state}"),

        Tables\Columns\TextColumn::make('vin')
            ->formatStateUsing(fn($state) => "VIN : {$state}"),
        Tables\Columns\TextColumn::make('engine_number')
            ->formatStateUsing(fn($state) => "Engine Number : {$state}"),

        Tables\Columns\TextColumn::make('bpkb_number')
            ->formatStateUsing(fn($state) => "BPKB Number : {$state}"),
        Tables\Columns\TextColumn::make('purchase_price')
            ->formatStateUsing(fn($state) => "Harga Beli : Rp " . number_format($state, 0, ',', '.')),

        Tables\Columns\TextColumn::make('sale_price')
            ->formatStateUsing(fn($state) => "Harga Jual : Rp " . number_format($state, 0, ',', '.')),
        Tables\Columns\TextColumn::make('status')
            ->formatStateUsing(fn($state) => "Status : {$state}"),
    ]),
])
    ->extraAttributes([
        'style' => 'background:white; border:1px solid #d1d5db; border-radius:4px; padding:10px; margin-bottom:10px;',
    ])

        ])
            ->filters([])
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make(),
            ])
            ->defaultPaginationPageOption(10);
    }

}
