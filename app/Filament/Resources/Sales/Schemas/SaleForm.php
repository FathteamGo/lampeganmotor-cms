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
            Select::make('user_id')
                ->label('Sales')
                ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Select::make('vehicle_id')
                ->label('Motor')
                ->options(function ($get, $record) {
                    $query = Vehicle::with(['vehicleModel', 'color'])
                        ->where('status', 'available')
                        ->whereDoesntHave('sale', function ($q) {
                            $q->where('status', '!=', 'cancel');
                        });

                    // saat edit, izinkan motor yang sekarang dipakai
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
                ->afterStateUpdated(function ($state, callable $set, $get) {
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

            DatePicker::make('sale_date')
                ->label('Tanggal')
                ->required()
                ->default(now()),

            TextInput::make('sale_price')
                ->label('OTR')
                ->numeric()
                ->prefix('Rp')
                ->required(),

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
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo'])),

            TextInput::make('dp_real')
                ->label('DP REAL')
                ->numeric()
                ->prefix('Rp')
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo'])),

            TextInput::make('remaining_payment')
                ->label('Sisa Pembayaran')
                ->numeric()
                ->prefix('Rp')
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo'])),

            DatePicker::make('due_date')
                ->label('Jatuh Tempo')
                ->visible(fn($get) => in_array($get('payment_method'), ['credit', 'cash_tempo'])),

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
                ->afterStateUpdated(function ($state, callable $set, $get, $record) {
                    if (!$record) return;

                    $vehicleId = $record->vehicle_id;

                    // Validasi duplicate status jika motor sudah ada
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

                    // Tambahkan catatan otomatis jika status cancel
                    if ($state === 'cancel') {
                        $set('notes', trim(($get('notes') ?? '') . "\n[Dibatalkan pada " . now()->format('d M Y H:i') . "]"));
                    }
                }),

            Textarea::make('notes')->label('Catatan')->columnSpanFull(),
        ]);
    }
}
