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

            /** 🔹 DATA KENDARAAN */
            ComponentsSection::make('Data Kendaraan')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('brand_name')
                        ->label('Merek Kendaraan')
                        ->required(fn($record) => !$record)
                        ->disabled(fn($record) => $record !== null),

                    TextInput::make('vehicle_model_name')
                        ->label('Model Kendaraan')
                        ->required(fn($record) => !$record)
                        ->disabled(fn($record) => $record !== null),

                    TextInput::make('type_name')
                        ->label('Tipe Unit')
                        ->required(fn($record) => !$record)
                        ->disabled(fn($record) => $record !== null),

                    TextInput::make('color_name')
                        ->label('Warna')
                        ->required(fn($record) => !$record)
                        ->disabled(fn($record) => $record !== null),

                    TextInput::make('year_name')
                        ->label('Tahun')
                        ->numeric()
                        ->minValue(2000)
                        ->maxValue(now()->year + 1)
                        ->required(fn($record) => !$record)
                        ->disabled(fn($record) => $record !== null),

                    TextInput::make('vin')
                        ->label('Nomor Rangka (VIN)')
                        ->required(fn($record) => !$record),

                    TextInput::make('engine_number')
                        ->label('Nomor Mesin')
                        ->required(fn($record) => !$record),

                    TextInput::make('license_plate')
                        ->label('Plat Nomor'),

                    TextInput::make('bpkb_number')
                        ->label('Nomor BPKB'),

                    TextInput::make('purchase_price')
                        ->label('Harga Beli')
                        ->required()
                        ->prefix('Rp')
                        ->extraInputAttributes([
                            'oninput' => "
                                clearTimeout(window.priceTimeout);
                                let n = this.value.replace(/[^0-9]/g, '');
                                if(n){
                                    this.value = new Intl.NumberFormat('id-ID').format(n);
                                } else {
                                    this.value = '';
                                }
                                window.priceTimeout = setTimeout(() => {
                                    this.dispatchEvent(new Event('change', { bubbles: true }));
                                }, 800);
                            "
                        ])
                        ->dehydrateStateUsing(fn($state) => $state ? preg_replace('/[^0-9]/', '', $state) : null)
                        ->reactive()
                        ->afterStateUpdated(fn($state, callable $set, callable $get) =>
                            $set('grand_total', self::calculateGrandTotal($get))
                        ),

                    TextInput::make('sale_price')
                        ->label('Harga Jual')
                        ->prefix('Rp')
                        ->extraInputAttributes([
                            'oninput' => "
                                let n = this.value.replace(/[^0-9]/g, '');
                                if(n){
                                    this.value = new Intl.NumberFormat('id-ID').format(n);
                                } else {
                                    this.value = '';
                                }
                            "
                        ])
                        ->dehydrateStateUsing(fn($state) => $state ? preg_replace('/[^0-9]/', '', $state) : null),

                    TextInput::make('down_payment')
                        ->label('DP')
                        ->prefix('Rp')
                        ->extraInputAttributes([
                            'oninput' => "
                                let n = this.value.replace(/[^0-9]/g, '');
                                if(n){
                                    this.value = new Intl.NumberFormat('id-ID').format(n);
                                } else {
                                    this.value = '';
                                }
                            "
                        ])
                        ->dehydrateStateUsing(fn($state) => $state ? preg_replace('/[^0-9]/', '', $state) : null),

                    TextInput::make('odometer')
                        ->label('Odometer (KM)')
                        ->extraInputAttributes([
                            'oninput' => "
                                let n = this.value.replace(/[^0-9]/g, '');
                                if(n){
                                    this.value = new Intl.NumberFormat('id-ID').format(n);
                                } else {
                                    this.value = '';
                                }
                            "
                        ])
                        ->dehydrateStateUsing(fn($state) => $state ? preg_replace('/[^0-9]/', '', $state) : null),

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
                        ->visible(fn($record) => !$record)
                        ->schema([
                            FileUpload::make('file')
                                ->label('Upload Foto')
                                ->image()
                                ->disk('public')
                                ->directory('vehicle-photos')
                                ->getUploadedFileNameForStorageUsing(fn($file) => uniqid('vehicle_') . '.webp')
                                ->nullable(),
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
                        ->reactive()
                        ->afterStateUpdated(fn(callable $set, callable $get) =>
                            $set('grand_total', self::calculateGrandTotal($get))
                        )
                        ->schema([
                            Select::make('category_id')
                                ->label('Kategori')
                                ->options(fn() => Category::pluck('name', 'id'))
                                ->searchable(),

                            TextInput::make('price')
                                ->label('Harga')
                                ->prefix('Rp')
                                ->extraInputAttributes([
                                    'oninput' => "
                                        clearTimeout(window.additionalTimeout);
                                        let n = this.value.replace(/[^0-9]/g, '');
                                        if(n){
                                            this.value = new Intl.NumberFormat('id-ID').format(n);
                                        } else {
                                            this.value = '';
                                        }
                                        window.additionalTimeout = setTimeout(() => {
                                            this.dispatchEvent(new Event('change', { bubbles: true }));
                                        }, 800);
                                    "
                                ])
                                ->dehydrateStateUsing(fn($state) => $state ? preg_replace('/[^0-9]/', '', $state) : null)
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
                        ->readOnly()
                        ->dehydrated(false)
                        ->reactive()
                        ->prefix('Rp')
                        ->extraInputAttributes([
                            'class' => 'text-green-600 font-bold text-lg',
                        ])
                        ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '0')
                        ->afterStateHydrated(fn(callable $set, callable $get) =>
                            $set('grand_total', self::calculateGrandTotal($get))
                        ),
                ]),
        ]);
    }

    /** 🔹 Hitung total harga beli + biaya tambahan */
    private static function calculateGrandTotal(callable $get): float
    {
        // Ambil harga beli (sudah dalam format angka mentah karena dehydrate)
        $purchasePrice = $get('purchase_price');
        $harga = floatval(is_string($purchasePrice) ? preg_replace('/[^0-9]/', '', $purchasePrice) : ($purchasePrice ?? 0));

        // Ambil biaya tambahan
        $tambahan = collect($get('additional_costs') ?? [])
            ->sum(function ($item) {
                $price = data_get($item, 'price', 0);
                return floatval(is_string($price) ? preg_replace('/[^0-9]/', '', $price) : $price);
            });

        return round($harga + $tambahan, 2);
    }
}