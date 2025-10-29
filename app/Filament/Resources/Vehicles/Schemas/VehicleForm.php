<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\VehicleModel;
use App\Models\Type;
use App\Models\Color;
use App\Models\Year;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([

                // ======== DATA DASAR ========
                Select::make('vehicle_model_id')
                    ->label('Model Kendaraan')
                    ->options(VehicleModel::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('type_id')
                    ->label('Tipe')
                    ->options(Type::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('color_id')
                    ->label('Warna')
                    ->options(Color::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('year_id')
                    ->label('Tahun')
                    ->options(Year::orderBy('year')->pluck('year', 'id'))
                    ->searchable()
                    ->required(),

                TextInput::make('vin')
                    ->label('Nomor Rangka (VIN)')
                    ->required()
                    ->maxLength(255)
                    ->rule(fn($record) => Rule::unique('vehicles', 'vin')->ignore($record))
                    ->validationMessages([
                        'unique' => 'Nomor Rangka (VIN) sudah terdaftar.',
                    ]),

                TextInput::make('engine_number')
                    ->label('Nomor Mesin')
                    ->required()
                    ->maxLength(255)
                    ->rule(fn($record) => Rule::unique('vehicles', 'engine_number')->ignore($record))
                    ->validationMessages([
                        'unique' => 'Nomor Mesin sudah terdaftar.',
                    ]),

                TextInput::make('license_plate')
                    ->label('Plat Nomor')
                    ->maxLength(255)
                    ->rule(fn($record) => Rule::unique('vehicles', 'license_plate')->ignore($record))
                    ->validationMessages([
                        'unique' => 'Plat Nomor sudah terdaftar.',
                    ]),

                TextInput::make('bpkb_number')
                    ->label('Nomor BPKB')
                    ->maxLength(255)
                    ->rule(fn($record) => Rule::unique('vehicles', 'bpkb_number')->ignore($record))
                    ->validationMessages([
                        'unique' => 'Nomor BPKB sudah terdaftar.',
                    ]),

                // ======== HARGA ========
                TextInput::make('purchase_price')
                    ->label('Harga Beli')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),

                TextInput::make('sale_price')
                    ->label('Harga Jual')
                    ->numeric()
                    ->prefix('Rp'),

                TextInput::make('down_payment')
                    ->label('DP (Nominal)')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->helperText('Masukkan nominal DP kendaraan (bukan persen).'),

                TextInput::make('odometer')
                    ->label('Odometer (KM)')
                    ->numeric()
                    ->minValue(0),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Tersedia',
                        'sold' => 'Terjual',
                        'in_repair' => 'Perbaikan',
                        'hold' => 'Ditahan',
                    ])
                    ->default('hold')
                    ->required(),

                // ======== INFO TAMBAHAN ========
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

                // ======== FOTO KENDARAAN ========
                Repeater::make('photos')
                    ->relationship()
                    ->label('Foto Kendaraan')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('path')
                            ->label('Gambar')
                            ->image()
                            ->disk('public')
                            ->directory('vehicle-photos')
                            ->required()
                            ->getUploadedFileNameForStorageUsing(fn($file) => uniqid('vehicle_') . '.webp')
                            ->saveUploadedFileUsing(function ($file) {
                                // Gunakan GD driver
                                $manager = new ImageManager(new Driver());

                                // Baca gambar
                                $image = $manager->read($file->getRealPath());

                                // Coba perbaiki orientasi jika dari kamera HP
                                try {
                                    $ext = strtolower($file->getClientOriginalExtension() ?? pathinfo($file->getClientName(), PATHINFO_EXTENSION));
                                    if (in_array($ext, ['jpg', 'jpeg']) && function_exists('exif_read_data')) {
                                        $exif = @exif_read_data($file->getRealPath());
                                        if (!empty($exif['Orientation'])) {
                                            switch ($exif['Orientation']) {
                                                case 3:
                                                    $image->rotate(180);
                                                    break;
                                                case 6:
                                                    $image->rotate(-90);
                                                    break;
                                                case 8:
                                                    $image->rotate(90);
                                                    break;
                                            }
                                        }
                                    }
                                } catch (\Throwable $e) {
                                    // Abaikan error EXIF
                                }

                                // Kompres ke WebP tapi pakai ukuran asli (no resize)
                                $encoded = $image->encodeByExtension('webp', 80);

                                // Simpan manual ke disk public
                                $path = 'vehicle-photos/' . uniqid('vehicle_') . '.webp';
                                Storage::disk('public')->put($path, (string) $encoded);

                                return $path;
                            }),

                        TextInput::make('caption')
                            ->label('Keterangan'),
                    ])
                    ->grid(2),
            ]);
    }
}
