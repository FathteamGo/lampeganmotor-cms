<?php
namespace App\Filament\Resources\Sales\Schemas;

use App\Models\Sale;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // Sales
            Select::make('user_id')
                ->label('Sales')
                ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),

            // Kendaraan
            Select::make('vehicle_id')
                ->label('Motor')
                ->options(function ($get, $record) {
                    $query = Vehicle::with(['vehicleModel', 'color'])
                        ->where('status', 'available')
                        ->whereDoesntHave('sale', function ($q) {
                            $q->whereIn('status', ['proses', 'kirim']);
                        });

                    // Allow current vehicle in edit mode
                    if ($record && $record->vehicle_id) {
                        $query->orWhere('id', $record->vehicle_id);
                    }

                    return $query->get()->mapWithKeys(fn($v) => [
                        $v->id => sprintf(
                            '%s | %s | %s',
                            $v->vehicleModel->name ?? 'Unknown',
                            $v->color->name ?? 'Unknown',
                            $v->license_plate ?? 'No Plate'
                        ),
                    ]);
                })
                ->required()
                ->searchable()
                ->afterStateUpdated(function ($state) {
                    if (! $state) {
                        return;
                    }

                    $exists = Sale::where('vehicle_id', $state)
                        ->whereIn('status', ['proses', 'kirim'])
                        ->exists();

                    if ($exists) {
                        throw ValidationException::withMessages([
                            'vehicle_id' => 'Motor ini masih terikat dengan penjualan aktif (belum cancel).',
                        ]);
                    }
                }),

            // Data Customer
            ComponentsSection::make('Data Customer')
                ->description('Data customer akan otomatis disimpan ke master Customer')
                ->schema([
                    TextInput::make('customer_name')->label('Nama Customer')->required(),
                    TextInput::make('customer_phone')->label('No. Telepon')->tel(),
                    TextInput::make('customer_address')->label('Alamat'),
                    TextInput::make('customer_instagram')->label('Instagram'),
                    TextInput::make('customer_tiktok')->label('TikTok'),
                ])
                ->columns(2),

            // Detail Penjualan
            DatePicker::make('sale_date')
                ->label('Tanggal')
                ->required()
                ->default(now()),

            // Input OTR dengan format ribuan yang responsif saat ketik
            TextInput::make('sale_price')
                ->label('OTR')
                ->prefix('Rp')
                ->required()
                ->placeholder('17.000.000')
                // Hydrate: ambil dari DB (int) lalu format jadi ribuan untuk ditampilkan
                ->afterStateHydrated(function ($state, callable $set) {
                    if ($state === null) return;
                    $clean = (int) floatval($state);
                    $set('sale_price', number_format($clean, 0, ',', '.'));
                })
                // Format ribuan real-time saat user ketik
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
                    ",
                ])
                // Dehydrate: hapus format ribuan, simpan ke DB sebagai integer
                ->dehydrateStateUsing(fn($state) =>
                    $state ? (int) preg_replace('/[^0-9]/', '', $state) : 0
                )
                ->live(onBlur: true)
                ->afterStateUpdated(fn($state, callable $set, callable $get) =>
                    $set('remaining_payment', self::calculateRemaining($get))
                ),

            Select::make('payment_method')
                ->label('Metode Pembayaran')
                ->options([
                    'cash'        => 'Cash',
                    'credit'      => 'Credit',
                    'tukartambah' => 'Tukar Tambah',
                    'cash_tempo'  => 'Cash Tempo',
                    'dana_tunai'  => 'Dana Tunai',
                ])
                ->default('cash')
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    if (in_array($state, ['credit', 'cash_tempo', 'dana_tunai'])) {
                        $set('remaining_payment', self::calculateRemaining($get));
                    } else {
                        $set('remaining_payment', 0);
                    }
                }),

            Select::make('leasing')
                ->label('Leasing')
                ->options([
                    'ADIRA' => 'ADIRA FINANCE',
                    'BAF'   => 'BAF (Bussan Auto Finance)',
                    'MTF'   => 'Mandali Utama Finance (MTF)',
                ])
                ->visible(fn($get) => $get('payment_method') === 'credit')
                ->required(fn($get) => $get('payment_method') === 'credit')
                ->reactive(),

            // DP PO dengan format ribuan
            TextInput::make('dp_po')
                ->label('DP PO')
                ->prefix('Rp')
                ->placeholder('2.000.000')
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo', 'dana_tunai']))
                ->afterStateHydrated(function ($state, callable $set) {
                    if ($state === null) return;
                    $clean = (int) floatval($state);
                    $set('dp_po', number_format($clean, 0, ',', '.'));
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
                    ",
                ])
                ->dehydrateStateUsing(fn($state) =>
                    $state ? (int) preg_replace('/[^0-9]/', '', $state) : 0
                )
                ->live(onBlur: true)
                ->afterStateUpdated(fn($state, callable $set, callable $get) =>
                    $set('remaining_payment', self::calculateRemaining($get))
                ),

            // DP REAL dengan format ribuan
            TextInput::make('dp_real')
                ->label('DP REAL')
                ->prefix('Rp')
                ->placeholder('1.500.000')
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo']))
                ->afterStateHydrated(function ($state, callable $set) {
                    if ($state === null) return;
                    $clean = (int) floatval($state);
                    $set('dp_real', number_format($clean, 0, ',', '.'));
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
                    ",
                ])
                ->dehydrateStateUsing(fn($state) =>
                    $state ? (int) preg_replace('/[^0-9]/', '', $state) : 0
                )
                ->live(onBlur: true)
                ->afterStateUpdated(fn($state, callable $set, callable $get) =>
                    $set('remaining_payment', self::calculateRemaining($get))
                ),

            // Pembayaran ke Nasabah (khusus Dana Tunai)
            TextInput::make('payment_to_customer')
                ->label('Pembayaran ke Nasabah')
                ->prefix('Rp')
                ->placeholder('14.000.000')
                ->visible(fn($get) => $get('payment_method') === 'dana_tunai')
                ->helperText('Jumlah yang dibayarkan ke nasabah')
                ->afterStateHydrated(function ($state, callable $set) {
                    if ($state === null) return;
                    $clean = (int) floatval($state);
                    $set('payment_to_customer', number_format($clean, 0, ',', '.'));
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
                    ",
                ])
                ->dehydrateStateUsing(fn($state) =>
                    $state ? (int) preg_replace('/[^0-9]/', '', $state) : 0
                )
                ->live(onBlur: true)
                ->afterStateUpdated(fn($state, callable $set, callable $get) =>
                    $set('remaining_payment', self::calculateRemaining($get))
                ),

            // Sisa Pembayaran (readonly, auto-calculated)
            TextInput::make('remaining_payment')
                ->label(fn($get) => $get('payment_method') === 'dana_tunai' ? 'Laba Penjualan' : 'Sisa Pembayaran')
                ->prefix('Rp')
                ->readOnly()
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo', 'dana_tunai']))
                ->helperText(fn($get) => $get('payment_method') === 'dana_tunai' 
                    ? 'OTR - DP PO - Pembayaran ke Nasabah' 
                    : null)
                ->formatStateUsing(fn($state) =>
                    $state ? number_format($state, 0, ',', '.') : '0'
                )
                ->dehydrateStateUsing(fn($state) =>
                    $state ? (int) preg_replace('/[^0-9]/', '', $state) : 0
                ),

            DatePicker::make('due_date')
                ->label('Jatuh Tempo')
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo'])),

            // Komisi & Info Tambahan
            Select::make('cmo_id')
                ->label('CMO / Mediator')
                ->relationship('cmo', 'name')
                ->searchable()
                ->preload()
                ->createOptionForm([
                    TextInput::make('name')
                        ->label('Nama CMO')
                        ->required(),
                ])
                ->createOptionUsing(function (array $data) {
                    return \App\Models\Cmo::create($data)->id;
                }),

            // Fee CMO dengan format ribuan
            TextInput::make('cmo_fee')
                ->label('Fee CMO')
                ->prefix('Rp')
                ->placeholder('500.000')
                ->afterStateHydrated(function ($state, callable $set) {
                    if ($state === null) return;
                    $clean = (int) floatval($state);
                    $set('cmo_fee', number_format($clean, 0, ',', '.'));
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
                    ",
                ])
                ->dehydrateStateUsing(fn($state) =>
                    $state ? (int) preg_replace('/[^0-9]/', '', $state) : 0
                ),

            // Komisi Langsung dengan format ribuan
            TextInput::make('direct_commission')
                ->label('Komisi Langsung')
                ->prefix('Rp')
                ->default(0)
                ->placeholder('300.000')
                ->afterStateHydrated(function ($state, callable $set) {
                    if ($state === null) return;
                    $clean = (int) floatval($state);
                    $set('direct_commission', number_format($clean, 0, ',', '.'));
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
                    ",
                ])
                ->dehydrateStateUsing(fn($state) =>
                    $state ? (int) preg_replace('/[^0-9]/', '', $state) : 0
                ),

            Select::make('order_source')
                ->label('Sumber Order')
                ->options([
                    'fb'      => 'Facebook',
                    'ig'      => 'Instagram',
                    'tiktok'  => 'TikTok',
                    'olx'     => 'OLX',
                    'walk_in' => 'Walk In',
                ]),

            TextInput::make('branch_name')->label('Cabang'),

            Select::make('result')
                ->label('Hasil')
                ->options([
                    'ACC'    => 'ACC',
                    'CASH'   => 'CASH',
                    'CANCEL' => 'CANCEL',
                ]),

            Select::make('status')
                ->label('Status')
                ->options([
                    'proses'  => 'Proses',
                    'kirim'   => 'Kirim',
                    'selesai' => 'Selesai',
                    'cancel'  => 'Cancel',
                ])
                ->default('proses')
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get, $record) {

                    // Hitung ulang remaining agar tidak null/non-numeric
                    $set('remaining_payment', self::calculateRemaining($get));

                    if (! $record) {
                        return;
                    }

                    $vehicleId = $record->vehicle_id;

                    if (in_array($state, ['kirim', 'selesai'])) {
                        $existing = Sale::where('vehicle_id', $vehicleId)
                            ->where('status', $state)
                            ->where('id', '!=', $record->id)
                            ->first();

                        if ($existing) {
                            throw ValidationException::withMessages([
                                'status' => "Motor ini sudah dijual kepada customer: {$existing->customer_name}.",
                            ]);
                        }
                    }

                    if ($state === 'cancel') {
                        $set('notes', trim(($get('notes') ?? '') . "\n[Dibatalkan pada " . now()->format('d M Y H:i') . "]"));
                    }
                }),

            Textarea::make('notes')->label('Catatan')->columnSpanFull(),
        ]);
    }

    /**
     * Hitung sisa pembayaran otomatis (selalu return float valid)
     * 
     * Rumus berdasarkan metode pembayaran:
     * - Credit / Cash Tempo: OTR - (DP PO + DP REAL) = Sisa Pembayaran
     * - Dana Tunai: OTR - DP PO - Pembayaran ke Nasabah = Laba Penjualan
     * - Cash / Tukar Tambah: Tidak ada sisa pembayaran
     * 
     * Sisa pembayaran ini yang nantinya masuk ke:
     * - Tunggakan konsumen (untuk Cash Tempo)
     * - Pembayaran dari leasing (untuk Credit)
     * - Laba Penjualan (untuk Dana Tunai)
     */
    private static function calculateRemaining(callable $get): float
    {
        $paymentMethod = $get('payment_method');
        $rawOtr        = $get('sale_price') ?? 0;
        $rawDpPo       = $get('dp_po') ?? 0;
        $rawDpReal     = $get('dp_real') ?? 0;
        $rawPaymentCustomer = $get('payment_to_customer') ?? 0;

        // Clean semua input dari format ribuan
        $otr             = floatval(preg_replace('/[^0-9]/', '', (string) $rawOtr));
        $dpPo            = floatval(preg_replace('/[^0-9]/', '', (string) $rawDpPo));
        $dpReal          = floatval(preg_replace('/[^0-9]/', '', (string) $rawDpReal));
        $paymentCustomer = floatval(preg_replace('/[^0-9]/', '', (string) $rawPaymentCustomer));

        // Perhitungan berbeda untuk Dana Tunai
        if ($paymentMethod === 'dana_tunai') {
            // Rumus: OTR - DP PO - Pembayaran ke Nasabah = Laba
            $remaining = $otr - $dpPo - $paymentCustomer;
        } else {
            // Rumus: OTR - (DP PO + DP REAL)
            $remaining = $otr - ($dpPo + $dpReal);
        }

        // Validasi hasil perhitungan
        if (! is_finite($remaining) || is_nan($remaining)) {
            return 0.0;
        }

        return max($remaining, 0.0);
    }
}