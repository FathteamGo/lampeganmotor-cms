<?php
namespace App\Filament\Pages;

use App\Models\Vehicle;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class InventoryReport extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'Report & Audit';
    protected static ?int $navigationSort                        = 3;
    protected static ?string $navigationLabel                    = 'Inventory Report';
    protected static ?string $title                              = 'Report & Audit';

    protected string $view = 'filament.pages.inventory-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vehicle::query()->with([
                    'vehicleModel.brand',
                    'type',
                    'year',
                    'color',
                    'photos',
                    'additionalCosts',
                ])
            )
            ->contentGrid([])
            ->columns([
                Split::make([
                    ImageColumn::make('photo_thumb')
                        ->getStateUsing(function ($record) {
                            $path = $record->photos->first()?->path;
                            return $path ? Storage::disk('public')->url($path) : null;
                        })
                        ->width(100)
                        ->height(150),

                    Grid::make(2)->schema([
                        TextColumn::make('displayName')
                            ->label('')
                            ->weight('bold')
                            ->extraAttributes([
                                'style' => 'font-size:14px; font-weight:600; grid-column: span 2; margin-bottom:6px;',
                            ]),

                        TextColumn::make('vehicleModel.brand.name')
                            ->formatStateUsing(fn($state) => "Brand : {$state}")
                            ->searchable(query: function (Builder $query, string $search) {
                                $query->whereHas('vehicleModel.brand', function (Builder $query) use ($search) {
                                    $query->where('name', 'like', "%{$search}%");
                                });
                            }),

                        TextColumn::make('vehicleModel.name')
                            ->formatStateUsing(fn($state) => "Model : {$state}")
                            ->searchable(query: function (Builder $query, string $search) {
                                $query->whereHas('vehicleModel', function (Builder $query) use ($search) {
                                    $query->where('name', 'like', "%{$search}%");
                                });
                            }),

                        TextColumn::make('type.name')
                            ->formatStateUsing(fn($state) => "Type : {$state}"),
                        TextColumn::make('color.name')
                            ->formatStateUsing(fn($state) => "Color : {$state}"),

                        TextColumn::make('year.year')
                            ->formatStateUsing(fn($state) => "Year : {$state}"),
                        TextColumn::make('license_plate')
                            ->formatStateUsing(fn($state) => "License Plate : {$state}")
                            ->searchable(),

                        TextColumn::make('vin')
                            ->formatStateUsing(fn($state) => "VIN : {$state}")
                            ->searchable(),

                        TextColumn::make('engine_number')
                            ->formatStateUsing(fn($state) => "Engine Number : {$state}"),
                        TextColumn::make('bpkb_number')
                            ->formatStateUsing(fn($state) => "BPKB Number : {$state}"),
                        TextColumn::make('purchase_price')
                            ->formatStateUsing(fn($state) => "Harga Beli : Rp " . number_format($state, 0, ',', '.')),
                        TextColumn::make('sale_price')
                            ->formatStateUsing(fn($state) => "Harga Jual : Rp " . number_format($state, 0, ',', '.')),
                        TextColumn::make('status')
                            ->formatStateUsing(fn($state) => "Status : {$state}"),
                        TextColumn::make('location')
                            ->formatStateUsing(fn($state) => "Location : {$state}"),
                    ]),
                ])
                    ->extraAttributes([
                        'style' => 'border:1px solid #d1d5db; border-radius:4px; padding:10px; margin-bottom:10px;',
                    ]),
            ])
            ->filters([])
            ->defaultPaginationPageOption(10);
    }
}
