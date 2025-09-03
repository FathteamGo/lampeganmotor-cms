<?php

namespace App\Filament\Pages;

use App\Models\Purchase;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class PurchaseReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|UnitEnum|null $navigationGroup = 'navigation.report_audit';
    protected static ?string $navigationLabel = 'navigation.purchase_report';
    protected static ?string $title = 'navigation.purchase_report_title';
    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.purchase-report';

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
        ini_set('max_execution_time', 300); // 5 menit

        return $table
            ->query(
                Purchase::query()->with([
                    'vehicle.vehicleModel.brand',
                    'vehicle.type',
                    'vehicle.color',
                    'vehicle.year',
                    'supplier',
                ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('navigation.invoice_number'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('purchase_date')
                    ->label(__('navigation.date'))
                    ->date('F d, Y'),

                // Supplier Info
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label(__('navigation.supplier'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('supplier.address')
                    ->label(__('navigation.address')),
                Tables\Columns\TextColumn::make('supplier.phone')
                    ->label(__('navigation.phone')),

                // Vehicle Info
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
                Tables\Columns\TextColumn::make('vehicle.status')
                    ->label(__('navigation.status')),

                // Purchase Info
                Tables\Columns\TextColumn::make('total_price')
                    ->money('IDR')
                    ->label(__('navigation.total_price')),
            ])
            ->filters([
                Tables\Filters\Filter::make('purchase_date')
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
                            ->when($data['from'] ?? null, fn($q, $from) => $q->whereDate('purchase_date', '>=', $from))
                            ->when($data['until'] ?? null, fn($q, $until) => $q->whereDate('purchase_date', '<=', $until));
                    }),
            ])
            ->paginated(false);
    }
}
