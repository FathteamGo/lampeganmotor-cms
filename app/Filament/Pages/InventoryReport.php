<?php

namespace App\Filament\Pages;

use App\Models\Vehicle;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use Illuminate\Database\Eloquent\Builder;

class InventoryReport extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'navigation.report_audit';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'navigation.inventory_report';
    protected static ?string $title = 'navigation.inventory_report';
    protected string $view = 'filament.pages.inventory-report';

    // 🔑 Multi bahasa untuk sidebar & title
    public static function getNavigationGroup(): ?string
    {
        return __(static::$navigationGroup);
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

    public static function getNavigationLabel(): string
    {
        return __(static::$navigationLabel);
    }

    public function getTitle(): string
    {
        return __(static::$title);
    }

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
            ->columns([
                Split::make([
                    ImageColumn::make('photo_thumb')
                        ->getStateUsing(fn ($record) => $record->photos->first()?->path
                            ? Storage::disk('public')->url($record->photos->first()?->path)
                            : null
                        )
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
                            ->formatStateUsing(fn($state) => "Brand: {$state}")
                            ->searchable(query: fn(Builder $query, string $search) => 
                                $query->whereHas('vehicleModel.brand', fn(Builder $q) => $q->where('name', 'like', "%{$search}%"))
                            ),
                        TextColumn::make('vehicleModel.name')
                            ->formatStateUsing(fn($state) => "Model: {$state}")
                            ->searchable(query: fn(Builder $query, string $search) =>
                                $query->whereHas('vehicleModel', fn(Builder $q) => $q->where('name', 'like', "%{$search}%"))
                            ),
                        TextColumn::make('type.name')->formatStateUsing(fn($state) => "Type: {$state}"),
                        TextColumn::make('color.name')->formatStateUsing(fn($state) => "Color: {$state}"),
                        TextColumn::make('year.year')->formatStateUsing(fn($state) => "Year: {$state}"),
                        TextColumn::make('license_plate')->formatStateUsing(fn($state) => "License Plate: {$state}")->searchable(),
                        TextColumn::make('vin')->formatStateUsing(fn($state) => "VIN: {$state}")->searchable(),
                        TextColumn::make('engine_number')->formatStateUsing(fn($state) => "Engine Number: {$state}"),
                        TextColumn::make('bpkb_number')->formatStateUsing(fn($state) => "BPKB Number: {$state}"),
                        TextColumn::make('purchase_price')->formatStateUsing(fn($state) => "Harga Beli: Rp ".number_format($state,0,',','.')),
                        TextColumn::make('sale_price')->formatStateUsing(fn($state) => "Harga Jual: Rp ".number_format($state,0,',','.')),
                        TextColumn::make('status')->formatStateUsing(fn($state) => "Status: {$state}"),
                        TextColumn::make('location')->formatStateUsing(fn($state) => "Location: {$state}"),
                    ]),
                ])
                ->extraAttributes([
                    'style' => 'border:1px solid #d1d5db; border-radius:4px; padding:10px; margin-bottom:10px;',
                ]),
            ])
            ->filters([])
            ->headerActions([
                ExportAction::make(),
            ])
            ->defaultPaginationPageOption(10);
    }
}
