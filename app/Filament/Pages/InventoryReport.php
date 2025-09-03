<?php

namespace App\Filament\Pages;

use App\Models\Vehicle;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Grid;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class InventoryReport extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'navigation.report_audit';
    protected static ?int $navigationSort                   = 3;
    protected static ?string $navigationLabel               = 'navigation.inventory_report';
    protected static ?string $title                         = 'navigation.inventory_report';

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
                        // Judul (span 2 kolom)
                        TextColumn::make('displayName')
                            ->label('')
                            ->weight('bold')
                            ->extraAttributes([
                                'style' => 'font-size:14px; font-weight:600; grid-column: span 2; margin-bottom:6px;',
                            ]),

                        // Kolom info kendaraan
                        Tables\Columns\TextColumn::make('vehicleModel.brand.name')
                            ->formatStateUsing(fn($state) => __('navigation.brand') . " : {$state}"),
                        Tables\Columns\TextColumn::make('vehicleModel.name')
                            ->formatStateUsing(fn($state) => __('navigation.model') . " : {$state}"),

                        Tables\Columns\TextColumn::make('type.name')
                            ->formatStateUsing(fn($state) => __('navigation.type') . " : {$state}"),
                        Tables\Columns\TextColumn::make('color.name')
                            ->formatStateUsing(fn($state) => __('navigation.color') . " : {$state}"),

                        Tables\Columns\TextColumn::make('year.year')
                            ->formatStateUsing(fn($state) => __('navigation.year') . " : {$state}"),
                        Tables\Columns\TextColumn::make('license_plate')
                            ->formatStateUsing(fn($state) => __('navigation.license_plate') . " : {$state}"),

                        Tables\Columns\TextColumn::make('vin')
                            ->formatStateUsing(fn($state) => __('navigation.vin') . " : {$state}"),
                        Tables\Columns\TextColumn::make('engine_number')
                            ->formatStateUsing(fn($state) => __('navigation.engine_number') . " : {$state}"),

                        Tables\Columns\TextColumn::make('bpkb_number')
                            ->formatStateUsing(fn($state) => __('navigation.bpkb_number') . " : {$state}"),
                        Tables\Columns\TextColumn::make('purchase_price')
                            ->formatStateUsing(fn($state) => __('navigation.purchase_price') . " : Rp " . number_format($state, 0, ',', '.')),

                        Tables\Columns\TextColumn::make('sale_price')
                            ->formatStateUsing(fn($state) => __('navigation.sale_price') . " : Rp " . number_format($state, 0, ',', '.')),
                        Tables\Columns\TextColumn::make('status')
                            ->formatStateUsing(fn($state) => __('navigation.status') . " : {$state}"),
                    ]),
                ])
                ->extraAttributes([
                    'style' => 'background:white; border:1px solid #d1d5db; border-radius:4px; padding:10px; margin-bottom:10px;',
                ]),
            ])
            ->filters([])
            ->headerActions([
                ExportAction::make(),
            ])
            ->defaultPaginationPageOption(10);
    }
}
