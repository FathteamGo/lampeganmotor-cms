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
                    ImageColumn::make('photos')
                        ->getStateUsing(fn($record) => $record->photos->take(2)->map(fn($photo) => asset('storage/' . $photo->path))->toArray())
                        ->width(300)
                        ->height(300)
                        ->extraAttributes([
                            'style' => 'object-fit:cover; margin-right:4px;',
                        ])
                        ->label('Foto'),


                    Grid::make(2)->schema([
                        TextColumn::make('displayName')
                            ->label('')
                            ->weight('bold')
                            ->extraAttributes([
                                'style' => 'font-size:14px; font-weight:600; grid-column: span 2; margin-bottom:6px;',
                            ]),

                        TextColumn::make('vehicleModel.brand.name')
                            ->formatStateUsing(fn($state) => __('tables.brand') . ": {$state}")
                            ->searchable(
                                query: fn(Builder $query, string $search) =>
                                $query->whereHas('vehicleModel.brand', fn(Builder $q) => $q->where('name', 'like', "%{$search}%"))
                            ),

                        TextColumn::make('vehicleModel.name')
                            ->formatStateUsing(fn($state) => __('tables.model') . ": {$state}")
                            ->searchable(
                                query: fn(Builder $query, string $search) =>
                                $query->whereHas('vehicleModel', fn(Builder $q) => $q->where('name', 'like', "%{$search}%"))
                            ),

                        TextColumn::make('type.name')
                            ->formatStateUsing(fn($state) => __('tables.type') . ": {$state}"),

                        TextColumn::make('color.name')
                            ->formatStateUsing(fn($state) => __('tables.color') . ": {$state}"),

                        TextColumn::make('year.year')
                            ->formatStateUsing(fn($state) => __('tables.year') . ": {$state}"),

                        TextColumn::make('license_plate')
                            ->formatStateUsing(fn($state) => __('tables.license_plate') . ": {$state}")
                            ->searchable(),

                        TextColumn::make('vin')
                            ->formatStateUsing(fn($state) => __('tables.vin') . ": {$state}")
                            ->searchable(),

                        TextColumn::make('engine_number')
                            ->formatStateUsing(fn($state) => __('tables.engine_number') . ": {$state}"),

                        TextColumn::make('bpkb_number')
                            ->formatStateUsing(fn($state) => __('tables.bpkb_number') . ": {$state}"),

                        TextColumn::make('purchase_price')
                            ->label(__('tables.purchase_price'))
                            ->money('idr', true),

                        TextColumn::make('sale_price')
                            ->label(__('tables.sale_price'))
                            ->money('idr', true),

                        TextColumn::make('status')
                            ->formatStateUsing(fn($state) => __('tables.status') . ": {$state}"),

                        TextColumn::make('location')
                            ->formatStateUsing(fn($state) => __('tables.location') . ": {$state}"),
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
