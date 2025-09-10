<?php
namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use App\Models\Vehicle;
use App\Models\Supplier;
use Carbon\Carbon;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Vehicle dropdown
                Select::make('vehicle_id')
                    ->label(__('tables.purchase_model'))
                    ->options(
                        Vehicle::with(['vehicleModel', 'color'])
                            ->get()
                            ->mapWithKeys(fn($vehicle) => [
                                $vehicle->id => sprintf(
                                    '%s | %s | %s',
                                    $vehicle->vehicleModel->name ?? 'Unknown Model',
                                    $vehicle->color->name ?? 'Unknown Color',
                                    $vehicle->license_plate ?? 'No Plate'
                                ),
                            ])
                    )
                    ->searchable()
                    ->required(),

                // Supplier dropdown
                Select::make('supplier_id')
                    ->label(__('tables.purchase_supplier'))
                    ->options(Supplier::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                // Tanggal pembelian
                DatePicker::make('purchase_date')
                    ->label(__('tables.purchase_date'))
                    ->required()
                    ->default(Carbon::now()),

                // Harga motor
                TextInput::make('total_price')
                    ->label(__('tables.purchase_total_price'))
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('grand_total', self::calculateGrandTotal($get));
                    }),

                // Biaya tambahan
                Repeater::make('additional_costs')
                    ->label('Biaya Tambahan')
                    ->relationship('additionalCosts')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('grand_total', self::calculateGrandTotal($get));
                    })
                    ->schema([
                        Select::make('category_id')
                            ->label('Kategori')
                            ->options(\App\Models\Category::pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        TextInput::make('price')
                            ->label('Harga')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $set('grand_total', self::calculateGrandTotal($get));
                            }),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->createItemButtonLabel('+'),

                // Grand total kayak TextInput tapi readonly
                TextInput::make('grand_total')
                    ->label('Total Pembayaran')
                    ->readOnly()
                    ->dehydrated(false) // ga nyimpen ke DB
                    ->reactive()
                    ->default(0)
                    ->afterStateHydrated(function ($set, $get) {
                        $set('grand_total', self::calculateGrandTotal($get));
                    })
                    ->suffix('Rp')
                    ->extraAttributes([
                        'class' => 'text-green-600 font-bold text-xl',
                    ]),

                // Catatan
                Textarea::make('notes')
                    ->label(__('tables.note'))
                    ->columnSpanFull(),
            ]);
    }

    private static function calculateGrandTotal($get): float
    {
        $motor = floatval($get('total_price') ?? 0);
        $additional = collect($get('additional_costs') ?? [])
            ->sum(fn ($item) => floatval($item['price'] ?? 0));

        return $motor + $additional;
    }

}
