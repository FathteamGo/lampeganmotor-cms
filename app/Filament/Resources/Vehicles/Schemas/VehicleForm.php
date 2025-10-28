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
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

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
                    ->numeric(),

                TextInput::make('sale_price')
                    ->label('Harga Jual')
                    ->numeric(),

                TextInput::make('dp_amount')
                    ->label('DP (Nominal)')
                    ->numeric()
                    ->minValue(0),

                TextInput::make('odometer')
                    ->label('Odometer')
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
                            ->getUploadedFileNameForStorageUsing(function ($file) {
                                // Nama file unik dengan ekstensi webp
                                return uniqid('vehicle_') . '.webp';
                            })
                            ->saveUploadedFileUsing(function ($file) {
                                // Gunakan Intervention Image untuk kompresi
                                $manager = new ImageManager(new Driver());

                                $image = $manager->read($file->getRealPath())
                                    ->resize(800, null, function ($constraint) {
                                        $constraint->aspectRatio();
                                        $constraint->upsize();
                                    })
                                    ->toWebp(50); // kompres ringan (50% kualitas)

                                // Simpan manual ke disk public
                                $path = 'vehicle-photos/' . uniqid('vehicle_') . '.webp';
                                Storage::disk('public')->put($path, (string) $image);

                                // return path agar disimpan ke DB
                                return $path;
                            }),

                        TextInput::make('caption')
                            ->label('Keterangan'),
                    ])
                    ->grid(2),
            ]);
    }
}
