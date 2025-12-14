<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([

                // ========= DATA DASAR =========
                TextInput::make('brand_name')
                    ->label('Merek Kendaraan')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Yamaha'),

                TextInput::make('vehicle_model_name')
                    ->label('Model Kendaraan')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Mio Z'),

                TextInput::make('type_name')
                    ->label('Tipe Kendaraan')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Matic'),

                TextInput::make('color_name')
                    ->label('Warna')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Merah'),

                TextInput::make('year_name')
                    ->label('Tahun')
                    ->required()
                    ->numeric()
                    ->placeholder('Contoh: 2020'),

                TextInput::make('vin')
                    ->label('Nomor Rangka (VIN)')
                    ->required()
                    ->maxLength(255)
                    ->rule(fn ($record) => Rule::unique('vehicles', 'vin')->ignore($record))
                    ->validationMessages([
                        'unique' => 'Nomor Rangka (VIN) sudah terdaftar.',
                    ]),

                TextInput::make('engine_number')
                    ->label('Nomor Mesin')
                    ->required()
                    ->maxLength(255)
                    ->rule(fn ($record) => Rule::unique('vehicles', 'engine_number')->ignore($record))
                    ->validationMessages([
                        'unique' => 'Nomor Mesin sudah terdaftar.',
                    ]),

                TextInput::make('license_plate')
                    ->label('Plat Nomor')
                    ->maxLength(255)
                    ->rule(fn ($record) => Rule::unique('vehicles', 'license_plate')->ignore($record))
                    ->validationMessages([
                        'unique' => 'Plat Nomor sudah terdaftar.',
                    ]),

                TextInput::make('bpkb_number')
                    ->label('Nomor BPKB')
                    ->maxLength(255)
                    ->rule(fn ($record) => Rule::unique('vehicles', 'bpkb_number')->ignore($record))
                    ->validationMessages([
                        'unique' => 'Nomor BPKB sudah terdaftar.',
                    ]),

                // ========= HARGA DENGAN FORMAT (FIX TOTAL) =========

                TextInput::make('purchase_price')
                    ->label('Harga Beli')
                    ->required()
                    ->prefix('Rp')
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
                    ->dehydrateStateUsing(fn ($state) => $state ? preg_replace('/[^0-9]/', '', $state) : null),

                TextInput::make('sale_price')
                    ->label('Harga Jual')
                    ->prefix('Rp')
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
                    ->dehydrateStateUsing(fn ($state) => $state ? preg_replace('/[^0-9]/', '', $state) : null),

                TextInput::make('down_payment')
                    ->label('DP (Nominal)')
                    ->prefix('Rp')
                    ->helperText('Masukkan nominal DP kendaraan (bukan persen).')
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
                    ->dehydrateStateUsing(fn ($state) => $state ? preg_replace('/[^0-9]/', '', $state) : null),

                TextInput::make('odometer')
                    ->label('Odometer (KM)')
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
                    ->dehydrateStateUsing(fn ($state) => $state ? preg_replace('/[^0-9]/', '', $state) : null),

                // ========= STATUS =========
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Tersedia',
                        'sold' => 'Terjual',
                        'in_repair' => 'Perbaikan',
                        'hold' => 'Ditahan',
                    ])
                    ->default('available')
                    ->required(),

                // ========= INFO TAMBAHAN =========
                TextInput::make('engine_specification')
                    ->label('Spesifikasi Mesin'),

                TextInput::make('location')
                    ->label('Lokasi'),

                RichEditor::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),

                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),

                // ========= FOTO =========
                Repeater::make('photos')
                    ->relationship()
                    ->label('Foto Kendaraan')
                    ->columnSpanFull()
                    ->grid(2)
                    ->schema([
                        FileUpload::make('path')
                            ->label('Gambar')
                            ->image()
                            ->disk('public')
                            ->directory('vehicle-photos')
                            ->nullable()
                            ->getUploadedFileNameForStorageUsing(
                                fn () => uniqid('vehicle_') . '.webp'
                            )
                            ->saveUploadedFileUsing(function ($file) {
                                $manager = new ImageManager(new Driver());
                                $image = $manager->read($file->getRealPath());

                                try {
                                    if (function_exists('exif_read_data')) {
                                        $exif = @exif_read_data($file->getRealPath());
                                        if (!empty($exif['Orientation'])) {
                                            switch ($exif['Orientation']) {
                                                case 3: $image->rotate(180); break;
                                                case 6: $image->rotate(-90); break;
                                                case 8: $image->rotate(90); break;
                                            }
                                        }
                                    }
                                } catch (\Throwable $e) {}

                                $encoded = $image->encodeByExtension('webp', 80);
                                $path = 'vehicle-photos/' . uniqid('vehicle_') . '.webp';
                                Storage::disk('public')->put($path, (string) $encoded);

                                return $path;
                            }),

                        TextInput::make('caption')
                            ->label('Keterangan'),
                    ]),
            ]);
    }
}
