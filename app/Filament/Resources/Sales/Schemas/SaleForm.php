<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\Sale;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // 🔹 Sales
            Select::make('user_id')
                ->label('Sales')
                ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),

            // 🔹 Kendaraan
            Select::make('vehicle_id')
                ->label('Motor')
                ->options(function ($get, $record) {
                    $query = Vehicle::with(['vehicleModel', 'color'])
                        ->where('status', 'available')
                        ->whereDoesntHave('sale', function ($q) {
                            $q->where('status', '!=', 'cancel');
                        });

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
                    if (!$state) return;

                    $exists = Sale::where('vehicle_id', $state)
                        ->where('status', '!=', 'cancel')
                        ->exists();

                    if ($exists) {
                        throw ValidationException::withMessages([
                            'vehicle_id' => 'Motor ini masih terikat dengan penjualan aktif (belum cancel).',
                        ]);
                    }
                }),

            // 🔹 Data Customer
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

            // 🔹 Detail Penjualan
            DatePicker::make('sale_date')
                ->label('Tanggal')
                ->required()
                ->default(now()),

            TextInput::make('sale_price')
                ->label('OTR')
                ->numeric()
                ->prefix('Rp')
                ->required()
                ->lazy() // update state saat blur
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
                ->reactive(),

            TextInput::make('dp_po')
                ->label('DP PO')
                ->numeric()
                ->prefix('Rp')
                ->lazy()
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo']))
                ->afterStateUpdated(fn($state, callable $set, callable $get) =>
                    $set('remaining_payment', self::calculateRemaining($get))
                ),

            TextInput::make('dp_real')
                ->label('DP REAL')
                ->numeric()
                ->prefix('Rp')
                ->lazy()
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo']))
                ->afterStateUpdated(fn($state, callable $set, callable $get) =>
                    $set('remaining_payment', self::calculateRemaining($get))
                ),

            TextInput::make('remaining_payment')
                ->label('Sisa Pembayaran')
                ->numeric()
                ->prefix('Rp')
                ->readOnly()
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo'])),

            DatePicker::make('due_date')
                ->label('Jatuh Tempo')
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo'])),

            // 🔹 Komisi & Info Tambahan
            TextInput::make('cmo')->label('CMO / Mediator'),
            TextInput::make('cmo_fee')->label('Fee CMO')->numeric()->prefix('Rp'),
            TextInput::make('direct_commission')->label('Komisi Langsung')->numeric()->prefix('Rp'),

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
                    if (!$record) return;

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

    /** 🔹 Hitung sisa pembayaran otomatis */
    private static function calculateRemaining(callable $get): float
    {
        $otr = floatval($get('sale_price') ?? 0);
        $dpPo = floatval($get('dp_po') ?? 0);
        $dpReal = floatval($get('dp_real') ?? 0);

        // Rumus OTR - DP PO + DP REAL
        return max($otr - $dpPo + $dpReal, 0);
    }
}
