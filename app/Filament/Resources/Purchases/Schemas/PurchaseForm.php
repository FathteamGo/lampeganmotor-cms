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
                        ->label('Harga Beli Motor')
                        ->required()
                        ->prefix('Rp')
                        ->extraInputAttributes([
                            'class' => 'text-right',
                            'x-data' => '{ rawValue: "" }',
                            'x-init' => '
                                rawValue = $el.value.replace(/\D/g, "");
                                $el.value = rawValue ? parseInt(rawValue).toLocaleString("id-ID") : "";
                            ',
                            'x-on:input.debounce.500ms' => '
                                rawValue = $el.value.replace(/\D/g, "");
                                let formatted = rawValue ? parseInt(rawValue).toLocaleString("id-ID") : "";
                                if ($el.value !== formatted) {
                                    let pos = $el.selectionStart;
                                    let oldLen = $el.value.length;
                                    $el.value = formatted;
                                    let newLen = formatted.length;
                                    $el.setSelectionRange(pos + (newLen - oldLen), pos + (newLen - oldLen));
                                }
                            ',
                            'x-on:blur' => '$el.dispatchEvent(new Event("change", { bubbles: true }))',
                        ])
                        ->dehydrateStateUsing(fn($state) => $state ? preg_replace('/\D/', '', $state) : null)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function($state, callable $set, callable $get) {
                            self::updateTotals($set, $get);
                        }),

                    TextInput::make('sale_price')
                        ->label('Harga Jual')
                        ->prefix('Rp')
                        ->extraInputAttributes([
                            'class' => 'text-right',
                            'x-data' => '{ rawValue: "" }',
                            'x-init' => '
                                rawValue = $el.value.replace(/\D/g, "");
                                $el.value = rawValue ? parseInt(rawValue).toLocaleString("id-ID") : "";
                            ',
                            'x-on:input.debounce.500ms' => '
                                rawValue = $el.value.replace(/\D/g, "");
                                let formatted = rawValue ? parseInt(rawValue).toLocaleString("id-ID") : "";
                                if ($el.value !== formatted) {
                                    let pos = $el.selectionStart;
                                    let oldLen = $el.value.length;
                                    $el.value = formatted;
                                    let newLen = formatted.length;
                                    $el.setSelectionRange(pos + (newLen - oldLen), pos + (newLen - oldLen));
                                }
                            ',
                        ])
                        ->dehydrateStateUsing(fn($state) => $state ? preg_replace('/\D/', '', $state) : null),

                    TextInput::make('down_payment')
                        ->label('DP')
                        ->prefix('Rp')
                        ->extraInputAttributes([
                            'class' => 'text-right',
                            'x-data' => '{ rawValue: "" }',
                            'x-init' => '
                                rawValue = $el.value.replace(/\D/g, "");
                                $el.value = rawValue ? parseInt(rawValue).toLocaleString("id-ID") : "";
                            ',
                            'x-on:input.debounce.500ms' => '
                                rawValue = $el.value.replace(/\D/g, "");
                                let formatted = rawValue ? parseInt(rawValue).toLocaleString("id-ID") : "";
                                if ($el.value !== formatted) {
                                    let pos = $el.selectionStart;
                                    let oldLen = $el.value.length;
                                    $el.value = formatted;
                                    let newLen = formatted.length;
                                    $el.setSelectionRange(pos + (newLen - oldLen), pos + (newLen - oldLen));
                                }
                            ',
                        ])
                        ->dehydrateStateUsing(fn($state) => $state ? preg_replace('/\D/', '', $state) : null),

                    TextInput::make('odometer')
                        ->label('Odometer (KM)')
                        ->extraInputAttributes([
                            'class' => 'text-right',
                            'x-data' => '{ rawValue: "" }',
                            'x-init' => '
                                rawValue = $el.value.replace(/\D/g, "");
                                $el.value = rawValue ? parseInt(rawValue).toLocaleString("id-ID") : "";
                            ',
                            'x-on:input.debounce.500ms' => '
                                rawValue = $el.value.replace(/\D/g, "");
                                let formatted = rawValue ? parseInt(rawValue).toLocaleString("id-ID") : "";
                                if ($el.value !== formatted) {
                                    let pos = $el.selectionStart;
                                    let oldLen = $el.value.length;
                                    $el.value = formatted;
                                    let newLen = formatted.length;
                                    $el.setSelectionRange(pos + (newLen - oldLen), pos + (newLen - oldLen));
                                }
                            ',
                        ])
                        ->dehydrateStateUsing(fn($state) => $state ? preg_replace('/\D/', '', $state) : null),

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
                                ->nullable(),
                            TextInput::make('caption')
                                ->label('Keterangan Foto'),
                        ])
                        ->grid(2)
                        ->createItemButtonLabel('+ Tambah Foto')
                        ->deleteAction(fn ($action) => $action->requiresConfirmation()),
                ]),

            /** 🔹 BIAYA TAMBAHAN */
            ComponentsSection::make('Biaya Tambahan')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Repeater::make('additional_costs')
                        ->label('Biaya Tambahan')
                        ->columns(2)
                        ->schema([
                            Select::make('category_id')
                                ->label('Kategori')
                                ->options(fn() => Category::pluck('name', 'id'))
                                ->searchable(),

                            TextInput::make('price')
                                ->label('Harga')
                                ->prefix('Rp')
                                ->extraInputAttributes([
                                    'class' => 'text-right',
                                    'x-data' => '{ rawValue: "" }',
                                    'x-init' => '
                                        rawValue = $el.value.replace(/\D/g, "");
                                        $el.value = rawValue ? parseInt(rawValue).toLocaleString("id-ID") : "";
                                    ',
                                    'x-on:input.debounce.500ms' => '
                                        rawValue = $el.value.replace(/\D/g, "");
                                        let formatted = rawValue ? parseInt(rawValue).toLocaleString("id-ID") : "";
                                        if ($el.value !== formatted) {
                                            let pos = $el.selectionStart;
                                            let oldLen = $el.value.length;
                                            $el.value = formatted;
                                            let newLen = formatted.length;
                                            $el.setSelectionRange(pos + (newLen - oldLen), pos + (newLen - oldLen));
                                        }
                                    ',
                                    'x-on:blur' => '$el.dispatchEvent(new Event("change", { bubbles: true }))',
                                ])
                                ->dehydrateStateUsing(fn($state) => $state ? preg_replace('/\D/', '', $state) : null)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function($state, callable $set, callable $get) {
                                    self::updateTotals($set, $get);
                                }),
                        ])
                        ->defaultItems(1)
                        ->createItemButtonLabel('+ Tambah Biaya')
                        ->deleteAction(fn ($action) => $action->requiresConfirmation()),
                ]),

            /** 🔹 RINGKASAN PEMBAYARAN */
            ComponentsSection::make('Ringkasan Pembayaran')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('purchase_price_display')
                        ->label('Harga Beli Motor')
                        ->readOnly()
                        ->dehydrated(false)
                        ->prefix('Rp')
                        ->extraInputAttributes([
                            'class' => 'text-blue-600 font-semibold text-right',
                        ])
                        ->default(fn(callable $get) => self::formatNumber($get('purchase_price'))),

                    TextInput::make('additional_costs_total')
                        ->label('Total Biaya Tambahan')
                        ->readOnly()
                        ->dehydrated(false)
                        ->prefix('Rp')
                        ->extraInputAttributes([
                            'class' => 'text-orange-600 font-semibold text-right',
                        ])
                        ->default(fn(callable $get) => number_format(self::calculateAdditionalTotal($get), 0, ',', '.')),

                    TextInput::make('total_purchase')
                        ->label('Harga Total Pembelian')
                        ->readOnly()
                        ->dehydrated(false)
                        ->prefix('Rp')
                        ->columnSpanFull()
                        ->extraInputAttributes([
                            'class' => 'text-green-600 font-bold text-xl text-right',
                        ])
                        ->default(fn(callable $get) => number_format(self::calculateGrandTotal($get), 0, ',', '.')),
                ]),
        ]);
    }

    /** 🔹 Update semua total */
    private static function updateTotals(callable $set, callable $get): void
    {
        $set('purchase_price_display', self::formatNumber($get('purchase_price')));
        $set('additional_costs_total', number_format(self::calculateAdditionalTotal($get), 0, ',', '.'));
        $set('total_purchase', number_format(self::calculateGrandTotal($get), 0, ',', '.'));
    }

    /** 🔹 Format angka ke ribuan */
    private static function formatNumber($value): string
    {
        if (!$value) return '0';
        $clean = is_string($value) ? preg_replace('/\D/', '', $value) : $value;
        return number_format(floatval($clean), 0, ',', '.');
    }

    /** 🔹 Hitung total biaya tambahan */
    private static function calculateAdditionalTotal(callable $get): float
    {
        return collect($get('additional_costs') ?? [])
            ->sum(function ($item) {
                $price = data_get($item, 'price', 0);
                return floatval(is_string($price) ? preg_replace('/\D/', '', $price) : $price);
            });
    }

    /** 🔹 Hitung total harga beli + biaya tambahan */
    private static function calculateGrandTotal(callable $get): float
    {
        $purchasePrice = $get('purchase_price');
        $harga = floatval(is_string($purchasePrice) ? preg_replace('/\D/', '', $purchasePrice) : ($purchasePrice ?? 0));
        $tambahan = self::calculateAdditionalTotal($get);
        return round($harga + $tambahan, 2);
    }
}