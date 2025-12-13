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
                            $q->where('status', '!=', 'cancel');
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
                        ->where('status', '!=', 'cancel')
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

            TextInput::make('sale_price')
                ->label('OTR')
                ->prefix('Rp')
                ->required()
                ->lazy()
                ->extraInputAttributes([
                    'oninput' => "
                        clearTimeout(window.salePriceTimeout);
                        let n = this.value.replace(/[^0-9]/g, '');
                        this.value = n ? new Intl.NumberFormat('id-ID').format(n) : '';
                        window.salePriceTimeout = setTimeout(() => {
                            this.dispatchEvent(new Event('change', { bubbles: true }));
                        }, 800);
                    ",
                ])
                ->dehydrateStateUsing(fn($state) =>
                    floatval(preg_replace('/[^0-9]/', '', (string) $state) ?: 0)
                )
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
                ])
                ->default('cash')
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    if (in_array($state, ['credit', 'cash_tempo'])) {
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
                    'MTF'   => 'Mandiri Utama Finance (MTF)',
                ])
                ->visible(fn($get) => $get('payment_method') === 'credit')
                ->required(fn($get) => $get('payment_method') === 'credit')
                ->reactive(),

            TextInput::make('dp_po')
                ->label('DP PO')
                ->prefix('Rp')
                ->lazy()
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo']))
                ->extraInputAttributes([
                    'oninput' => "
                        clearTimeout(window.dpTimeout);
                        let n = this.value.replace(/[^0-9]/g, '');
                        this.value = n ? new Intl.NumberFormat('id-ID').format(n) : '';
                        window.dpTimeout = setTimeout(() => {
                            this.dispatchEvent(new Event('change', { bubbles: true }));
                        }, 800);
                    ",
                ])
                ->dehydrateStateUsing(fn($state) =>
                    floatval(preg_replace('/[^0-9]/', '', (string) $state) ?: 0)
                )
                ->afterStateUpdated(fn($state, callable $set, callable $get) =>
                    $set('remaining_payment', self::calculateRemaining($get))
                ),

            TextInput::make('dp_real')
                ->label('DP REAL')
                ->prefix('Rp')
                ->lazy()
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo']))
                ->extraInputAttributes([
                    'oninput' => "
                        clearTimeout(window.dpRealTimeout);
                        let n = this.value.replace(/[^0-9]/g, '');
                        this.value = n ? new Intl.NumberFormat('id-ID').format(n) : '';
                        window.dpRealTimeout = setTimeout(() => {
                            this.dispatchEvent(new Event('change', { bubbles: true }));
                        }, 800);
                    ",
                ])
                ->dehydrateStateUsing(fn($state) =>
                    floatval(preg_replace('/[^0-9]/', '', (string) $state) ?: 0)
                )
                ->afterStateUpdated(fn($state, callable $set, callable $get) =>
                    $set('remaining_payment', self::calculateRemaining($get))
                ),

            TextInput::make('remaining_payment')
                ->label('Sisa Pembayaran')
                ->prefix('Rp')
                ->readOnly()
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo']))
                ->formatStateUsing(fn($state) =>
                    $state ? number_format($state, 0, ',', '.') : '0'
                )
                ->dehydrateStateUsing(fn($state) =>
                    floatval(preg_replace('/[^0-9]/', '', (string) $state) ?: 0)
                ),

            DatePicker::make('due_date')
                ->label('Jatuh Tempo')
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo'])),

            // Komisi & Info Tambahan
            TextInput::make('cmo')->label('CMO / Mediator'),

            TextInput::make('cmo_fee')
                ->label('Fee CMO')
                ->prefix('Rp')
                ->lazy()
                ->extraInputAttributes([
                    'oninput' => "
                        let n = this.value.replace(/[^0-9]/g, '');
                        this.value = n ? new Intl.NumberFormat('id-ID').format(n) : '';
                    ",
                ])
                ->dehydrateStateUsing(fn($state) =>
                    floatval(preg_replace('/[^0-9]/', '', (string) $state) ?: 0)
                ),

            TextInput::make('direct_commission')
                ->label('Komisi Langsung')
                ->prefix('Rp')
                ->lazy()
                ->default(0)
                ->extraInputAttributes([
                    'oninput' => "
                        let n = this.value.replace(/[^0-9]/g, '');
                        this.value = n ? new Intl.NumberFormat('id-ID').format(n) : '';
                    ",
                ])
                ->dehydrateStateUsing(fn($state) =>
                    floatval(preg_replace('/[^0-9]/', '', (string) $state) ?: 0)
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

                    // hitung ulang remaining agar tidak null/non-numeric
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

    /** Hitung sisa pembayaran otomatis (selalu return float valid) */
    private static function calculateRemaining(callable $get): float
    {
        $rawOtr    = $get('sale_price') ?? 0;
        $rawDpPo   = $get('dp_po') ?? 0;
        $rawDpReal = $get('dp_real') ?? 0;

        $otr    = floatval(preg_replace('/[^0-9]/', '', (string) $rawOtr));
        $dpPo   = floatval(preg_replace('/[^0-9]/', '', (string) $rawDpPo));
        $dpReal = floatval(preg_replace('/[^0-9]/', '', (string) $rawDpReal));

        $remaining = $otr - ($dpPo + $dpReal);

        if (! is_finite($remaining) || is_nan($remaining)) {
            return 0.0;
        }

        return max($remaining, 0.0);
    }
}
