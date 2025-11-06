<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\Category;
use App\Models\Supplier;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            /** 🔹 DATA PEMBELIAN */
            ComponentsSection::make('Data Pembelian')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('supplier_id')
                        ->label('Pemasok')
                        ->options(fn() => Supplier::pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    DatePicker::make('purchase_date')
                        ->label('Tanggal Pembelian')
                        ->default(Carbon::now())
                        ->required(),

                    Textarea::make('notes')
                        ->label('Catatan Pembelian')
                        ->columnSpanFull(),
                ]),

            /** 🔹 DATA KENDARAAN BARU (MANUAL SEMUA) */
            ComponentsSection::make('Data Kendaraan')
                ->columns(2)
                ->columnSpanFull()
                ->visible(fn($record) => !$record)
                ->schema([
                    TextInput::make('vehicle_model_name')
                        ->label('Model Kendaraan')
                        ->required(),

                    TextInput::make('type_name')
                        ->label('Tipe Unit')
                        ->required(),

                    TextInput::make('color_name')
                        ->label('Warna')
                        ->required(),

                    TextInput::make('year_name')
                        ->label('Tahun')
                        ->numeric()
                        ->minValue(2000)
                        ->maxValue(now()->year + 1)
                        ->required(),

                    TextInput::make('vin')
                        ->label('Nomor Rangka (VIN)')
                        ->required(),

                    TextInput::make('engine_number')
                        ->label('Nomor Mesin')
                        ->required(),

                    TextInput::make('license_plate')
                        ->label('Plat Nomor'),

                    TextInput::make('bpkb_number')
                        ->label('Nomor BPKB'),

                    TextInput::make('purchase_price')
                        ->label('Harga Beli')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->default(0)
                        ->reactive()
                        ->afterStateUpdated(fn($state, callable $set, callable $get) =>
                            $set('grand_total', self::calculateGrandTotal($get))
                        ),

                    TextInput::make('sale_price')
                        ->label('Harga Jual')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0),

                    TextInput::make('down_payment')
                        ->label('DP')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0),

                    TextInput::make('odometer')
                        ->label('Odometer (KM)')
                        ->numeric(),

                    TextInput::make('engine_specification')
                        ->label('Spesifikasi Mesin'),

                    TextInput::make('location')
                        ->label('Lokasi'),

                    Textarea::make('vehicle_notes')
                        ->label('Catatan Kendaraan')
                        ->rows(2)
                        ->columnSpanFull(),

                    /** 📸 FOTO KENDARAAN */
                    Repeater::make('photos')
                        ->label('Foto Kendaraan')
                        ->columnSpanFull()
                        ->schema([
                            FileUpload::make('file')
                                ->label('Upload Foto')
                                ->image()
                                ->disk('public')
                                ->directory('vehicle-photos')
                                ->getUploadedFileNameForStorageUsing(fn($file) => uniqid('vehicle_') . '.webp')
                                ->required(),
                            TextInput::make('caption')
                                ->label('Keterangan Foto'),
                        ])
                        ->grid(2)
                        ->createItemButtonLabel('+ Tambah Foto'),
                ]),

            /** 🔹 BIAYA TAMBAHAN */
            ComponentsSection::make('Biaya Tambahan')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Repeater::make('additional_costs')
                        ->label('Biaya Tambahan')
                        ->columns(2)
                        ->reactive() // penting!
                        ->afterStateUpdated(fn(callable $set, callable $get) =>
                            $set('grand_total', self::calculateGrandTotal($get))
                        )
                        ->schema([
                            Select::make('category_id')
                                ->label('Kategori')
                                ->options(fn() => Category::pluck('name', 'id'))
                                ->searchable()
                                ->required(),

                            TextInput::make('price')
                                ->label('Harga')
                                ->numeric()
                                ->default(0)
                                ->reactive()
                                ->afterStateUpdated(fn($state, callable $set, callable $get) =>
                                    $set('grand_total', self::calculateGrandTotal($get))
                                ),
                        ])
                        ->defaultItems(1)
                        ->createItemButtonLabel('+ Tambah Biaya'),
                ]),

            /** 🔹 TOTAL PEMBAYARAN */
            ComponentsSection::make('Total Pembayaran')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('grand_total')
                        ->label('Total Pembayaran')
                        ->numeric()
                        ->readOnly()
                        ->dehydrated(false)
                        ->reactive()
                        ->default(0)
                        ->suffix('Rp')
                        ->extraAttributes([
                            'class' => 'text-green-600 font-bold text-lg',
                        ])
                        ->afterStateHydrated(fn(callable $set, callable $get) =>
                            $set('grand_total', self::calculateGrandTotal($get))
                        ),
                ]),
        ]);
    }

    /** 🔹 Hitung total harga beli + biaya tambahan */
    private static function calculateGrandTotal(callable $get): float
    {
        $harga = floatval($get('purchase_price') ?? 0);
        $tambahan = collect($get('additional_costs') ?? [])
            ->sum(fn($item) => floatval(data_get($item, 'price', 0)));

        return round($harga + $tambahan, 2);
    }
}
