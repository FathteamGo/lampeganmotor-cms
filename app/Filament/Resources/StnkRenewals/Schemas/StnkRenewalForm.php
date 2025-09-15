<?php

namespace App\Filament\Resources\StnkRenewals\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use App\Models\Customer;
use App\Models\StnkRenewal;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class StnkRenewalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                // Tanggal
                DatePicker::make('tgl')
                    ->label('Tanggal')
                    ->default(now())
                    ->required()
                    ->validationMessages([
                        'required' => 'Tanggal wajib diisi!',
                    ]),

                // Nomor Polisi
                TextInput::make('license_plate')
                    ->label('Nomor Polisi')
                    ->required()
                    ->maxLength(20)
                    ->rule(fn ($record) => Rule::unique('stnk_renewals', 'license_plate')
                        ->ignore($record)
                        ->where(fn ($query) => $query->whereNotIn('status', ['done'])))
                    ->validationMessages([
                        'required' => 'Nomor Polisi wajib diisi!',
                        'unique' => 'Nomor Polisi sudah ada dengan status pending atau progress!',
                    ]),

                // Atas Nama STNK
                TextInput::make('atas_nama_stnk')
                    ->label('Atas Nama STNK')
                    ->required()
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => 'Atas Nama STNK wajib diisi!',
                    ]),

                // Customer
                Select::make('customer_id')
                    ->label('Customer')
                    ->options(fn () => Customer::orderBy('name')->pluck('name','id')->toArray())
                    ->searchable()
                    ->required()
                    ->validationMessages([
                        'required' => 'Customer wajib dipilih!',
                    ]),

                // Total Pajak + Jasa
                TextInput::make('total_pajak_jasa')
                    ->label('Total Pajak + Jasa')
                    ->numeric()
                    ->default(0)
                    ->reactive()
                    ->lazy()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $dp = $get('dp') ?? 0;
                        $bayar = $get('pembayaran_ke_samsat') ?? 0;
                        $set('sisa_pembayaran', $state - $dp);
                        $set('margin_total', $state - $bayar);
                    }),

                // DP
                TextInput::make('dp')
                    ->label('DP')
                    ->integer()
                    ->default(0)
                    ->reactive()
                    ->lazy()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $total = $get('total_pajak_jasa') ?? 0;
                        $bayar = $get('pembayaran_ke_samsat') ?? 0;
                        $set('sisa_pembayaran', $total - $state);
                        $set('margin_total', $total - $bayar);
                    }),

                // Pembayaran ke Samsat
                TextInput::make('pembayaran_ke_samsat')
                    ->label('Pembayaran ke Samsat')
                    ->integer()
                    ->default(0)
                    ->reactive()
                    ->lazy()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $total = $get('total_pajak_jasa') ?? 0;
                        $dp = $get('dp') ?? 0;
                        $set('sisa_pembayaran', $total - $dp);
                        $set('margin_total', $total - $state);
                    }),

                // Sisa Pembayaran
                TextInput::make('sisa_pembayaran')
                    ->label('Sisa Pembayaran')
                    ->integer()
                    ->disabled()
                    ->dehydrated()
                    ->default(0),

                // Margin
                TextInput::make('margin_total')
                    ->label('Margin')
                    ->integer()
                    ->disabled()
                    ->dehydrated()
                    ->default(0),

                // Tanggal Diambil
                DatePicker::make('diambil_tgl')
                    ->label('Tanggal Diambil')
                    ->nullable(),

                // Status
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'progress' => 'Progress',
                        'done' => 'Done',
                    ])
                    ->default('pending')
                    ->required()
                    ->validationMessages([
                        'required' => 'Status wajib dipilih!',
                    ]),
            ]);
    }
}
