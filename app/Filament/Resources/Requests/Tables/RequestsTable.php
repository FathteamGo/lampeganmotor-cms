<?php

namespace App\Filament\Resources\Requests\Tables;

use App\Models\Request as VehicleRequest;
use App\Models\Vehicle;
use App\Models\Type;
use App\Models\Color;
use App\Services\WhatsAppService;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                VehicleRequest::query()
                    // hanya tampilkan request yang BELUM punya vehicle
                    ->whereNull('vehicle_id')
                    ->with(['supplier', 'brand', 'vehicleModel', 'year', 'photos'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('year.year')->label('Year')->sortable()->searchable(),
                TextColumn::make('supplier.name')->label('Name')->sortable()->searchable(),
                TextColumn::make('supplier.phone')->label('Phone')->sortable()->searchable(),
                TextColumn::make('brand.name')->label('Merk')->sortable()->searchable(),
                TextColumn::make('vehicleModel.name')->label('Model')->sortable()->searchable(),
                TextColumn::make('odometer')->label('Odometer')->sortable(),
                ImageColumn::make('photos.0.path')->label('Photo')->disk('public')->square(),
                TextColumn::make('license_plate')->label('Plate')->toggleable()->searchable(),
                TextColumn::make('notes')->label('Note')->limit(40)->tooltip(fn ($s) => $s),

                SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'hold'      => 'Hold',
                        'available' => 'Available',
                        'in_repair' => 'In_Repair',
                        'sold'      => 'Sold',
                    ])
                    ->selectablePlaceholder(false)
                    ->rules(['required']),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'hold'      => 'Hold',
                        'available' => 'Available',
                        'in_repair' => 'In_Repair',
                        'sold'      => 'Sold',
                    ])
                    ->default('hold'),

                SelectFilter::make('brand_id')
                    ->label('Merk')
                    ->relationship('brand', 'name'),

                SelectFilter::make('year_id')
                    ->label('Tahun')
                    ->relationship('year', 'year'),
            ])
            ->actions([
                ViewAction::make()
                    ->label('View')
                    ->icon('heroicon-o-eye'),

                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->form([
                        // ======== REQUIRED relations ========
                        Forms\Components\Select::make('type_id')
                            ->label('Type')
                            ->options(fn () => Type::orderBy('name')->pluck('name','id'))
                            ->required(),

                        Forms\Components\Select::make('color_id')
                            ->label('Color')
                            ->options(fn () => Color::orderBy('name')->pluck('name','id'))
                            ->required(),

                        // ======== UNIQUE & REQUIRED in vehicles ========
                        Forms\Components\TextInput::make('vin')
                            ->label('VIN')
                            ->required()
                            ->maxLength(255)
                            ->rule(Rule::unique('vehicles','vin')),

                        Forms\Components\TextInput::make('engine_number')
                            ->label('Engine No')
                            ->required()
                            ->maxLength(255)
                            ->rule(Rule::unique('vehicles','engine_number')),

                        // ======== OPTIONAL uniques ========
                        Forms\Components\TextInput::make('license_plate')
                            ->label('License Plate')
                            ->maxLength(255)
                            ->default(fn (VehicleRequest $r) => $r->license_plate)
                            ->placeholder(fn (VehicleRequest $r) => $r->license_plate)
                            ->rule(
                                Rule::unique('vehicles', 'license_plate')
                                    ->where(fn ($q) => $q->whereNotNull('license_plate'))
                            )
                            ->nullable(),

                        Forms\Components\TextInput::make('bpkb_number')
                            ->label('BPKB Number')
                            ->maxLength(255)
                            ->rule(
                                Rule::unique('vehicles','bpkb_number')
                                    ->where(fn ($q) => $q->whereNotNull('bpkb_number'))
                            ),

                        // ======== PRICES ========
                        Forms\Components\TextInput::make('purchase_price')
                            ->label('Purchase Price')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        Forms\Components\TextInput::make('sale_price')
                            ->label('Sale Price')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),

                        // ======== ODOMETER ========
                        Forms\Components\TextInput::make('odometer')
                            ->label('Odometer (KM)')
                            ->numeric()
                            ->minValue(0)
                            ->default(fn (VehicleRequest $r) => (int) $r->odometer),

                        // ======== STATUS VEHICLE ========
                        Forms\Components\Select::make('vehicle_status')
                            ->label('Vehicle Status')
                            ->options([
                                'hold'      => 'Hold',
                                'available' => 'Available',
                                'in_repair' => 'In_Repair',
                                'sold'      => 'Sold',
                            ])
                            ->default('hold')
                            ->required(),

                        // ======== FREE TEXTS ========
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->nullable(),

                        Forms\Components\TextInput::make('dp_percentage')
                            ->label('DP Percentage')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->nullable(),

                        Forms\Components\Textarea::make('engine_specification')
                            ->label('Engine Specification')
                            ->rows(2)
                            ->nullable(),

                        Forms\Components\Textarea::make('location')
                            ->label('Location')
                            ->rows(2)
                            ->nullable(),

                        Forms\Components\Textarea::make('notes_vehicle')
                            ->label('Notes (Vehicle)')
                            ->rows(2)
                            ->default(fn (VehicleRequest $r) => $r->notes)
                            ->nullable(),

                        // ======== NOTIF ========
                        Forms\Components\Toggle::make('send_whatsapp')
                            ->label('Kirim WhatsApp ke supplier')
                            ->default(true),
                    ])
                    ->action(function (array $data, VehicleRequest $record) {
                        DB::transaction(function () use ($data, $record) {
                            // 1) buat vehicle baru
                            $vehicle = Vehicle::create([
                                'vehicle_model_id'     => $record->vehicle_model_id,
                                'type_id'              => $data['type_id'],
                                'color_id'             => $data['color_id'],
                                'year_id'              => $record->year_id,
                                'vin'                  => $data['vin'],
                                'engine_number'        => $data['engine_number'],
                                'license_plate'        => $data['license_plate'] ?? $record->license_plate,
                                'bpkb_number'          => $data['bpkb_number'] ?? null,
                                'purchase_price'       => $data['purchase_price'],
                                'sale_price'           => $data['sale_price'] ?? null,
                                'odometer'             => isset($data['odometer']) ? (int) $data['odometer'] : ($record->odometer ?? null),
                                'status'               => $data['vehicle_status'] ?? 'hold',
                                'description'          => $data['description'] ?? null,
                                'dp_percentage'        => $data['dp_percentage'] ?? null,
                                'engine_specification' => $data['engine_specification'] ?? null,
                                'location'             => $data['location'] ?? null,
                                'notes'                => $data['notes_vehicle'] ?? ($record->notes ?? null),
                            ]);

                            // 2) tautkan foto
                            foreach ($record->photos as $i => $photo) {
                                $photo->update([
                                    'vehicle_id'  => $vehicle->id,
                                    'photo_order' => $i,
                                ]);
                            }

                            // 3) update request
                            $record->update([
                                'status'     => 'available',
                                'vehicle_id' => $vehicle->id,
                            ]);

                            $record->delete();

                            // 4) kirim WA
                            $waSent = false;
                            if (!empty($data['send_whatsapp'])) {
                                $waSent = app(WhatsAppService::class)->sendText(
                                    $record->supplier->phone,
                                    "Halo {$record->supplier->name},\n".
                                    "Pengajuan motor Anda ({$record->brand->name} {$record->vehicleModel->name} {$record->year->year}, plat {$record->license_plate}) telah kami APPROVE dan diproses.\n".
                                    "Terima kasih."
                                );
                            }

                            // 5) toast notifikasi
                            Notification::make()
                                ->title('Request approved')
                                ->body(
                                    "Vehicle #{$vehicle->id} dibuat (status: {$vehicle->status}). ".
                                    ($waSent ? 'WhatsApp terkirim ✅' : 'WhatsApp tidak terkirim ❌')
                                )
                                ->success()
                                ->send();
                        });
                    })
                    ->visible(fn (VehicleRequest $r) => $r->status !== 'converted'),
            ])
            ->actionsColumnLabel('Actions')
            ->actionsAlignment('start')
            ->emptyStateHeading('No requests')
            ->emptyStateDescription('Belum ada data request.')
            ->striped();
    }
}
