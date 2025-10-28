<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Models\User;
use App\Models\Vehicle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Schemas\Schema;

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
                ->options(
                    Vehicle::with(['vehicleModel','color'])
                        ->where('status','available')
                        ->whereDoesntHave('sale')
                        ->get()
                        ->mapWithKeys(fn($v)=>[$v->id => sprintf('%s | %s | %s',
                            $v->vehicleModel->name ?? 'Unknown',
                            $v->color->name ?? 'Unknown',
                            $v->license_plate ?? 'No Plate'
                        )])
                )
                ->searchable()
                ->required()
                ->unique('sales','vehicle_id'),

            // ===== DATA CUSTOMER (MANUAL INPUT) =====
            ComponentsSection::make('Data Customer')
                ->description('Data customer akan otomatis disimpan ke master Customer')
                ->schema([
                    TextInput::make('customer_name')
                        ->label('Nama Customer')
                        ->required()
                        ->maxLength(255),

                    // TextInput::make('customer_nik')
                    //     ->label('NIK')
                    //     ->maxLength(20),

                    TextInput::make('customer_phone')
                        ->label('No. Telepon')
                        ->tel()
                        ->maxLength(20),

                    TextInput::make('customer_address')
                        ->label('Alamat')
                        ->maxLength(500)
                        ->columnSpan(2),

                    TextInput::make('customer_instagram')
                        ->label('Instagram')
                        ->placeholder('@username atau URL')
                        ->maxLength(255),

                    TextInput::make('customer_tiktok')
                        ->label('TikTok')
                        ->placeholder('@username atau URL')
                        ->maxLength(255),
                ])
                ->columns(2),

            DatePicker::make('sale_date')
                ->label('Tanggal')
                ->required()
                ->default(now()),

            TextInput::make('sale_price')
                ->label('OTR')
                ->numeric()
                ->minValue(0)
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
                ->minValue(0)
                ->prefix('Rp')
                ->visible(fn ($get) => in_array($get('payment_method'), ['credit', 'cash_tempo'])),

            TextInput::make('dp_real')
                ->label('DP REAL')
                ->numeric()
                ->minValue(0)
                ->prefix('Rp')
                ->visible(fn ($get) => in_array($get('payment_method'), ['credit', 'cash_tempo'])),

            TextInput::make('remaining_payment')
                ->label('Sisa Pembayaran')
                ->numeric()
                ->minValue(0)
                ->prefix('Rp')
                ->visible(fn ($get) => in_array($get('payment_method'), ['credit', 'cash_tempo'])),

            DatePicker::make('due_date')
                ->label('Jatuh Tempo')
                ->visible(fn ($get) => in_array($get('payment_method'), ['credit', 'cash_tempo'])),

            TextInput::make('cmo')
                ->label('CMO / Mediator')
                ->maxLength(255),

            TextInput::make('cmo_fee')
                ->label('Fee CMO')
                ->numeric()
                ->minValue(0)
                ->prefix('Rp'),

            TextInput::make('direct_commission')
                ->label('Komisi Langsung')
                ->numeric()
                ->minValue(0)
                ->prefix('Rp'),

            Select::make('order_source')
                ->label('Sumber Order')
                ->options([
                    'fb'      => 'Facebook',
                    'ig'      => 'Instagram',
                    'tiktok'  => 'TikTok',
                    'olx'     => 'OLX',
                    'walk_in' => 'Walk In',
                ])
                ->searchable()
                ->placeholder('Pilih sumber order'),

            TextInput::make('branch_name')
                ->label('Cabang')
                ->maxLength(255),

            Select::make('result')
                ->label('Hasil')
                ->options([
                    'ACC'    => 'ACC',
                    'CASH'   => 'CASH',
                    'CANCEL' => 'CANCEL',
                ])
                ->searchable(),

            Select::make('status')
                ->label('Status')
                ->options([
                    'proses'  => 'Proses',
                    'kirim'   => 'Kirim',
                    'selesai' => 'Selesai',
                ])
                ->default('proses')
                ->required(),

            Textarea::make('notes')
                ->label('Note')
                ->columnSpanFull(),
        ]);
    }
}