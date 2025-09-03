<?php

namespace App\Filament\Pages;

use App\Models\Sale;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SalesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|UnitEnum|null $navigationGroup = 'navigation.report_audit';
    protected static ?string $navigationLabel = 'navigation.sales_report';
    protected static ?string $title = 'navigation.sales_report_title';
    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.sales-report';

    // 🔑 Multi bahasa untuk sidebar & title
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


    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sale::query()
                    ->with([
                        'vehicle.vehicleModel.brand',
                        'vehicle.type',
                        'vehicle.color',
                        'vehicle.year',
                        'customer',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('navigation.invoice_number'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('sale_date')
                    ->label(__('navigation.date'))
                    ->date('F d, Y'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('navigation.customer'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('vehicle.vehicleModel.brand.name')
                    ->label(__('navigation.brand')),
                Tables\Columns\TextColumn::make('vehicle.type.name')
                    ->label(__('navigation.type')),
                Tables\Columns\TextColumn::make('vehicle.vehicleModel.name')
                    ->label(__('navigation.model')),
                Tables\Columns\TextColumn::make('vehicle.color.name')
                    ->label(__('navigation.color')),
                Tables\Columns\TextColumn::make('vehicle.year.year')
                    ->label(__('navigation.year')),
                Tables\Columns\TextColumn::make('vehicle.vin')
                    ->label(__('navigation.vin')),
                Tables\Columns\TextColumn::make('vehicle.license_plate')
                    ->label(__('navigation.license_plate')),

                Tables\Columns\TextColumn::make('sale_price')
                    ->money('IDR')
                    ->label(__('navigation.sale_price')),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('navigation.payment_method')),
            ])
            ->filters([
                Tables\Filters\Filter::make('sale_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label(__('navigation.start_date'))
                            ->default(now()->startOfMonth()),

                        Forms\Components\DatePicker::make('until')
                            ->label(__('navigation.end_date'))
                            ->default(now()->endOfMonth()),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn($q, $from) => $q->whereDate('sale_date', '>=', $from))
                            ->when($data['until'] ?? null, fn($q, $until) => $q->whereDate('sale_date', '<=', $until));
                    }),
            ])
            ->paginated(false);
    }
}
