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
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            
            /* ================= DATA PEMBELIAN ================= */
            Section::make('Data Pembelian')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('supplier_id')
                        ->label('Supplier')
                        ->searchable()
                        ->options(Supplier::pluck('name', 'id'))
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('Nama Supplier')
                                ->placeholder('PT. Maju Jaya Motor')
                                ->required(),
                        ])
                        ->createOptionUsing(fn ($data) =>
                            Supplier::create(['name' => $data['name']])->id
                        )
                        ->required()
                        ->placeholder('Pilih atau ketik supplier'),

                    DatePicker::make('purchase_date')
                        ->label('Tanggal Pembelian')
                        ->default(Carbon::now())
                        ->placeholder('Pilih tanggal')
                        ->required(),

                    Textarea::make('notes')
                        ->label('Catatan Pembelian')
                        ->placeholder('Tulis catatan pembelian (opsional)')
                        ->columnSpanFull(),
                ]),

            /* ================= DATA KENDARAAN ================= */
            Section::make('Data Kendaraan')
                ->description(fn($record) => $record ? '⚠️ Data kendaraan tidak dapat diubah saat edit. Untuk mengubah data kendaraan, silakan edit di menu Master Kendaraan.' : null)
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('brand_name')
                        ->label('Merek')
                        ->placeholder('Honda')
                        ->required(fn($record) => !$record)
                        ->readOnly(fn($record) => $record !== null),

                    TextInput::make('vehicle_model_name')
                        ->label('Model')
                        ->placeholder('Vario')
                        ->required(fn($record) => !$record)
                        ->readOnly(fn($record) => $record !== null),

                    TextInput::make('type_name')
                        ->label('Tipe')
                        ->placeholder('Matic')
                        ->required(fn($record) => !$record)
                        ->readOnly(fn($record) => $record !== null),

                    TextInput::make('color_name')
                        ->label('Warna')
                        ->placeholder('Hitam')
                        ->required(fn($record) => !$record)
                        ->readOnly(fn($record) => $record !== null),

                    TextInput::make('year_name')
                        ->label('Tahun')
                        ->numeric()
                        ->minValue(1900)
                        ->maxValue(now()->year + 1)
                        ->placeholder('2024')
                        ->required(fn($record) => !$record)
                        ->readOnly(fn($record) => $record !== null),

                    TextInput::make('vin')
                        ->label('Nomor Rangka')
                        ->placeholder('MH1JFH115HK123456')
                        ->required(fn($record) => !$record)
                        ->readOnly(fn($record) => $record !== null),

                    TextInput::make('engine_number')
                        ->label('Nomor Mesin')
                        ->placeholder('JFH1E1234567')
                        ->required(fn($record) => !$record)
                        ->readOnly(fn($record) => $record !== null),

                    TextInput::make('license_plate')
                        ->label('Plat Nomor')
                        ->placeholder('D 1234 ABC')
                        ->readOnly(fn($record) => $record !== null),

                    TextInput::make('bpkb_number')
                        ->label('Nomor BPKB')
                        ->placeholder('A1234567890')
                        ->readOnly(fn($record) => $record !== null),

                    TextInput::make('engine_specification')
                        ->label('Spesifikasi Mesin')
                        ->placeholder('125cc, 4-Tak, SOHC')
                        ->readOnly(fn($record) => $record !== null),

                    TextInput::make('location')
                        ->label('Lokasi')
                        ->placeholder('Showroom A - Rak 2')
                        ->readOnly(fn($record) => $record !== null),

                    TextInput::make('odometer')
                        ->label('Odometer (KM)')
                        ->placeholder('12.500')
                        ->readOnly(fn($record) => $record !== null)
                        ->extraInputAttributes([
                            'oninput' => "
                                const input = this;
                                const start = input.selectionStart;
                                const oldLength = input.value.length;

                                let raw = input.value.replace(/[^0-9]/g, '');
                                let formatted = raw
                                    ? new Intl.NumberFormat('id-ID').format(raw)
                                    : '';

                                input.value = formatted;

                                const newLength = formatted.length;
                                const diff = newLength - oldLength;
                                const newPos = Math.max(start + diff, 0);

                                input.setSelectionRange(newPos, newPos);
                            "
                        ])
                        ->dehydrateStateUsing(fn($state) => $state ? preg_replace('/[^0-9]/', '', $state) : null),

                    Textarea::make('vehicle_notes')
                        ->label('Catatan Kendaraan')
                        ->placeholder('Kondisi motor, kelengkapan surat, dll')
                        ->rows(2)
                        ->columnSpanFull()
                        ->readOnly(fn($record) => $record !== null),
                ]),

            /* ================= HARGA ================= */
            Section::make('Harga Kendaraan')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('purchase_price')
                        ->label('Harga Beli Motor')
                        ->required()
                        ->prefix('Rp')
                        ->placeholder('15.500.000')
                        ->extraInputAttributes([
                            'oninput' => "
                                const input = this;
                                const start = input.selectionStart;
                                const oldLength = input.value.length;

                                let raw = input.value.replace(/[^0-9]/g, '');
                                let formatted = raw
                                    ? new Intl.NumberFormat('id-ID').format(raw)
                                    : '';

                                input.value = formatted;

                                const newLength = formatted.length;
                                const diff = newLength - oldLength;
                                const newPos = Math.max(start + diff, 0);

                                input.setSelectionRange(newPos, newPos);
                            "
                        ])
                        ->dehydrateStateUsing(fn($state) => $state ? (int) preg_replace('/[^0-9]/', '', $state) : null)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function($state, callable $set, callable $get) {
                            self::updateTotals($set, $get);
                        }),

                    TextInput::make('sale_price')
                        ->label('Harga Jual')
                        ->prefix('Rp')
                        ->placeholder('17.000.000')
                        ->extraInputAttributes([
                            'oninput' => "
                                const input = this;
                                const start = input.selectionStart;
                                const oldLength = input.value.length;

                                let raw = input.value.replace(/[^0-9]/g, '');
                                let formatted = raw
                                    ? new Intl.NumberFormat('id-ID').format(raw)
                                    : '';

                                input.value = formatted;

                                const newLength = formatted.length;
                                const diff = newLength - oldLength;
                                const newPos = Math.max(start + diff, 0);

                                input.setSelectionRange(newPos, newPos);
                            "
                        ])
                        ->dehydrateStateUsing(fn($state) => $state ? (int) preg_replace('/[^0-9]/', '', $state) : null),

                    TextInput::make('down_payment')
                        ->label('DP')
                        ->prefix('Rp')
                        ->placeholder('2.000.000')
                        ->extraInputAttributes([
                            'oninput' => "
                                const input = this;
                                const start = input.selectionStart;
                                const oldLength = input.value.length;

                                let raw = input.value.replace(/[^0-9]/g, '');
                                let formatted = raw
                                    ? new Intl.NumberFormat('id-ID').format(raw)
                                    : '';

                                input.value = formatted;

                                const newLength = formatted.length;
                                const diff = newLength - oldLength;
                                const newPos = Math.max(start + diff, 0);

                                input.setSelectionRange(newPos, newPos);
                            "
                        ])
                        ->dehydrateStateUsing(fn($state) => $state ? (int) preg_replace('/[^0-9]/', '', $state) : null),
                ]),

            /* ================= FOTO KENDARAAN ================= */
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
                        ->label('Keterangan Foto')
                        ->placeholder('Tampak depan, samping kiri, dll'),
                ])
                ->grid(2)
                ->createItemButtonLabel('+ Tambah Foto'),

            /* ================= BIAYA TAMBAHAN ================= */
    Section::make('Biaya Tambahan')
    ->description('Kosongkan jika tidak ada biaya tambahan')
    ->columnSpanFull()
    ->schema([
        Repeater::make('additionalCosts')
            ->relationship()
            ->label('Rincian Biaya Tambahan')
            ->columns(2)
            ->schema([
                Select::make('category_id')
                    ->label('Kategori')
                    ->searchable()
                    ->options(Category::pluck('name', 'id'))
                    ->placeholder('Pilih atau buat kategori baru')
                    ->createOptionForm([
                        TextInput::make('name')
                            ->placeholder('Contoh: STNK, Pajak, Service')
                            ->required(),
                    ])
                    ->createOptionUsing(fn ($data) =>
                        Category::create(['name' => $data['name']])->id
                    )
                    ->required(),

                TextInput::make('price')
                    ->label('Harga')
                    ->prefix('Rp')
                    ->placeholder('350.000')
                    ->required()

                    /**  FIX EDIT: buang .00 dari DB */
                    ->afterStateHydrated(function ($state, callable $set) {
                        if ($state === null) return;

                        // buang desimal .00
                        $clean = (int) floatval($state);

                        // format ribuan
                        $set('price', number_format($clean, 0, ',', '.'));
                    })

                    ->extraInputAttributes([
                        'oninput' => "
                            const input = this;
                            const start = input.selectionStart;
                            const oldLength = input.value.length;

                            let raw = input.value.replace(/[^0-9]/g, '');
                            let formatted = raw
                                ? new Intl.NumberFormat('id-ID').format(raw)
                                : '';

                            input.value = formatted;

                            const newLength = formatted.length;
                            const diff = newLength - oldLength;
                            const newPos = Math.max(start + diff, 0);

                            input.setSelectionRange(newPos, newPos);
                        "
                    ])

                    /**  DB tetap INT, TANPA .00 */
                    ->dehydrateStateUsing(
                        fn ($state) => $state
                            ? (int) preg_replace('/[^0-9]/', '', $state)
                            : 0
                    )

                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        self::updateTotals($set, $get);
                    }),
            ])
            ->defaultItems(0)
            ->createItemButtonLabel('+ Tambah Biaya')
            ->deleteAction(fn ($action) => $action->requiresConfirmation()),
    ]),


            /* ================= RINGKASAN PEMBAYARAN ================= */
            Section::make('Ringkasan Pembayaran')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('purchase_price_display')
                        ->label('Harga Motor')
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
                        ->default(fn(callable $get) => self::formatNumber(self::calculateAdditionalTotal($get))),

                    TextInput::make('total_purchase')
                        ->label('Harga Total Pembelian')
                        ->readOnly()
                        ->dehydrated(false)
                        ->prefix('Rp')
                        ->columnSpanFull()
                        ->extraInputAttributes([
                            'class' => 'text-green-600 font-bold text-xl text-right',
                        ])
                        ->default(fn(callable $get) => self::formatNumber(self::calculateGrandTotal($get))),
                ]),
        ]);
    }

    /** 🔹 Update semua total */
    private static function updateTotals(callable $set, callable $get): void
    {
        $set('purchase_price_display', self::formatNumber($get('purchase_price')));
        $set('additional_costs_total', self::formatNumber(self::calculateAdditionalTotal($get)));
        $set('total_purchase', self::formatNumber(self::calculateGrandTotal($get)));
    }

    /** 🔹 Format angka ke ribuan */
    private static function formatNumber($value): string
    {
        if (!$value) return '0';
        
        // Kalau sudah string formatted (ada titik), clean dulu
        if (is_string($value) && strpos($value, '.') !== false) {
            $value = preg_replace('/[^0-9]/', '', $value);
        }
        
        $clean = is_string($value) ? preg_replace('/[^0-9]/', '', $value) : $value;
        return number_format((float) $clean, 0, ',', '.');
    }

    /** 🔹 Hitung total biaya tambahan */
    private static function calculateAdditionalTotal(callable $get): float
    {
        return collect($get('additionalCosts') ?? [])
            ->sum(function ($item) {
                $price = data_get($item, 'price', 0);
                $clean = is_string($price) ? preg_replace('/[^0-9]/', '', $price) : $price;
                return (float) $clean;
            });
    }

    /** 🔹 Hitung total harga beli + biaya tambahan */
    private static function calculateGrandTotal(callable $get): float
    {
        $purchasePrice = $get('purchase_price');
        $clean = is_string($purchasePrice) ? preg_replace('/[^0-9]/', '', $purchasePrice) : ($purchasePrice ?? 0);
        $harga = (float) $clean;
        $tambahan = self::calculateAdditionalTotal($get);
        return $harga + $tambahan;
    }
}