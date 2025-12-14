<?php
namespace App\Filament\Resources\StnkRenewals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as ComponentsSection;
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
                    ->rule(fn($record) => Rule::unique('stnk_renewals', 'license_plate')
                            ->ignore($record)
                            ->where(fn($query) => $query->whereNotIn('status', ['done'])))
                    ->validationMessages([
                        'required' => 'Nomor Polisi wajib diisi!',
                        'unique'   => 'Nomor Polisi sudah ada dengan status pending atau progress!',
                    ]),

                // Atas Nama STNK
                TextInput::make('atas_nama_stnk')
                    ->label('Atas Nama STNK')
                    ->required()
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => 'Atas Nama STNK wajib diisi!',
                    ]),
                ComponentsSection::make('Data Customer')
                    ->description('Data customer akan otomatis disimpan ke master Customer')
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('Nama Customer')
                            ->required() 
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'Nama customer wajib diisi!',
                            ]),

                        TextInput::make('customer_nik')->label('NIK')->maxLength(20)->nullable(),

                        TextInput::make('customer_phone')->label('No. HP')->tel()->nullable(),

                        TextInput::make('customer_address')->label('Alamat')->nullable(),

                        TextInput::make('customer_instagram')->label('Instagram')->nullable(),

                        TextInput::make('customer_tiktok')->label('TikTok')->nullable(),
                    ])
                    ->columns(2),

                // Jenis Pekerjaan
                Select::make('jenis_pekerjaan')
                    ->label('Jenis Pekerjaan')
                    ->options([
                        'bbn'          => 'BBN Balik Nama',
                        'cetak_ganti'  => 'Cetak Ganti STNK / Plat',
                        'perpanjangan' => 'Perpanjangan Tahunan',
                    ])
                    ->required()
                    ->validationMessages([
                        'required' => 'Jenis pekerjaan wajib dipilih!',
                    ]),

                // Upload Foto STNK
                FileUpload::make('foto_stnk')
                    ->label('Foto STNK')
                    ->image()
                    ->directory('uploads/stnk')
                    ->nullable(),

                // Total Pajak + Jasa
                TextInput::make('total_pajak_jasa')
                    ->label('Total Pajak + Jasa')
                    ->prefix('Rp')
                    ->reactive()
                    ->lazy()
                    ->extraInputAttributes([
                        'oninput' => "
                            let n = this.value.replace(/[^0-9]/g,'');
                            this.value = n ? new Intl.NumberFormat('id-ID').format(n) : '';
                        ",
                    ])
                    ->dehydrateStateUsing(fn($state) => (int) preg_replace('/[^0-9]/', '', $state))
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $total  = (int) preg_replace('/[^0-9]/', '', $state ?? 0);
                        $dp     = (int) preg_replace('/[^0-9]/', '', $get('dp') ?? 0);
                        $vendor = (int) preg_replace('/[^0-9]/', '', $get('payvendor') ?? 0);

                        $set('sisa_pembayaran', $total - $dp);
                        $set('margin_total', $total - $vendor);
                    }),

                // DP / Dibayar
                TextInput::make('dp')
                    ->label('DP / Dibayar')
                    ->prefix('Rp')
                    ->reactive()
                    ->lazy()
                    ->extraInputAttributes([
                        'oninput' => "
                            let n = this.value.replace(/[^0-9]/g,'');
                            this.value = n ? new Intl.NumberFormat('id-ID').format(n) : '';
                        ",
                    ])
                    ->dehydrateStateUsing(fn($state) => (int) preg_replace('/[^0-9]/', '', $state))
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $total  = (int) preg_replace('/[^0-9]/', '', $get('total_pajak_jasa') ?? 0);
                        $dp     = (int) preg_replace('/[^0-9]/', '', $state ?? 0);
                        $vendor = (int) preg_replace('/[^0-9]/', '', $get('payvendor') ?? 0);

                        $set('sisa_pembayaran', $total - $dp);
                        $set('margin_total', $total - $vendor);
                    }),

                // Pembayaran ke Samsat
                TextInput::make('pembayaran_ke_samsat')
                    ->label('Pembayaran ke Samsat')
                    ->prefix('Rp')
                    ->reactive()
                    ->lazy()
                    ->extraInputAttributes([
                        'oninput' => "
                            let n = this.value.replace(/[^0-9]/g,'');
                            this.value = n ? new Intl.NumberFormat('id-ID').format(n) : '';
                        ",
                    ])
                    ->dehydrateStateUsing(fn($state) => (int) preg_replace('/[^0-9]/', '', $state)),

                // Nama Vendor
                TextInput::make('vendor')
                    ->label('Nama Vendor')
                    ->required()
                    ->maxLength(255)
                    ->default('-'),

                // Pembayaran ke Vendor
                TextInput::make('payvendor')
                    ->label('Pembayaran ke Vendor')
                    ->prefix('Rp')
                    ->reactive()
                    ->lazy()
                    ->extraInputAttributes([
                        'oninput' => "
                            let n = this.value.replace(/[^0-9]/g,'');
                            this.value = n ? new Intl.NumberFormat('id-ID').format(n) : '';
                        ",
                    ])
                    ->dehydrateStateUsing(fn($state) => (int) preg_replace('/[^0-9]/', '', $state))
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $total  = (int) preg_replace('/[^0-9]/', '', $get('total_pajak_jasa') ?? 0);
                        $vendor = (int) preg_replace('/[^0-9]/', '', $state ?? 0);

                        $set('margin_total', $total - $vendor);
                    }),

                // Sisa Pembayaran
                TextInput::make('sisa_pembayaran')
                    ->label('Sisa Pembayaran')
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated()
                    ->formatStateUsing(fn($state) => $state ? number_format((int) $state, 0, ',', '.') : '0'),

                // Margin (Laba)
                TextInput::make('margin_total')
                    ->label('Margin (Laba)')
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated()
                    ->formatStateUsing(fn($state) => $state ? number_format((int) $state, 0, ',', '.') : '0'),

                // Tanggal Diambil
                DatePicker::make('diambil_tgl')->label('Tanggal Diambil')->nullable(),

                // Status
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Pending',
                        'progress' => 'Progress',
                        'done'     => 'Done',
                    ])
                    ->default('pending')
                    ->required()
                    ->validationMessages([
                        'required' => 'Status wajib dipilih!',
                    ]),
            ]);
    }
}
