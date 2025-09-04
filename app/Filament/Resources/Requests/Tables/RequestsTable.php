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
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class RequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                VehicleRequest::query()
                    // ->whereNull('vehicle_id') // DIHAPUS: supaya request tetap tampil setelah approve
                    ->with(['supplier', 'brand', 'vehicleModel', 'year', 'photos'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('year.year')->label(('year'))->sortable()->searchable(),
                TextColumn::make('supplier.name')->label(('supplier_name'))->sortable()->searchable(),
                TextColumn::make('supplier.phone')->label(('phone'))->sortable()->searchable(),
                TextColumn::make('brand.name')->label(('brand'))->sortable()->searchable(),
                TextColumn::make('vehicleModel.name')->label(('model'))->sortable()->searchable(),
                TextColumn::make('odometer')->label(('odometer'))->sortable(),

                // Foto: konversi path relatif -> URL publik
                ImageColumn::make('photo_thumb')
                    ->label(('photo'))
                    ->getStateUsing(function (VehicleRequest $r) {
                        $path = $r->photos->first()?->path; // "requests/{id}/file.png"
                        return $path ? Storage::disk('public')->url($path) : null;
                    })
                    ->square()
                    ->height(48)
                    ->width(64),

                TextColumn::make('license_plate')->label(('plate'))->toggleable()->searchable(),

                TextColumn::make('notes')
                    ->label(('note'))
                    ->limit(40)
                    ->tooltip(fn ($state) => (string) $state),

                // status request (hold / available / in_repair / sold / converted / rejected)
                TextColumn::make('status')
                    ->label(('status'))
                    ->badge()
                    ->colors([
                        'warning'   => 'hold',
                        'success'   => 'converted',
                        'danger'    => 'rejected',
                        'info'      => 'available',
                        'gray'      => 'in_repair',
                        'secondary' => 'sold',
                    ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(('status'))
                    ->options([
                        'hold'      => __('status_hold'),
                        'available' => __('status_available'),
                        'in_repair' => __('status_in_repair'),
                        'sold'      => __('status_sold'),
                        'converted' => __('status_converted'),
                        'rejected'  => __('status_rejected'),
                    ]),
                SelectFilter::make('brand_id')
                    ->label(('brand'))
                    ->relationship('brand', 'name'),
                SelectFilter::make('year_id')
                    ->label(('year'))
                    ->relationship('year', 'year'),
            ])
            ->actions([
                ViewAction::make()
                    ->label(('view'))
                    ->icon('heroicon-o-eye'),

                // ======== APPROVE ========
                Action::make('approve')
                    ->label(('approve'))
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn (VehicleRequest $r) => ! in_array($r->status, ['converted', 'rejected']))
                    ->form([
                        Forms\Components\Select::make('type_id')
                            ->label(('tables.type'))
                            ->options(fn () => Type::orderBy('name')->pluck('name','id'))
                            ->required(),

                        Forms\Components\Select::make('color_id')
                            ->label(('tables.color'))
                            ->options(fn () => Color::orderBy('name')->pluck('name','id'))
                            ->required(),

                        Forms\Components\TextInput::make('vin')
                            ->label(('tables.vin'))
                            ->required()
                            ->maxLength(255)
                            ->rule(Rule::unique('vehicles','vin')),

                        Forms\Components\TextInput::make('engine_number')
                            ->label(('tables.engine_number'))
                            ->required()
                            ->maxLength(255)
                            ->rule(Rule::unique('vehicles','engine_number')),

                        Forms\Components\TextInput::make('license_plate')
                            ->label(('tables.license_plate'))
                            ->maxLength(255)
                            ->default(fn (VehicleRequest $r) => $r->license_plate)
                            ->placeholder(fn (VehicleRequest $r) => $r->license_plate)
                            ->rule(
                                Rule::unique('vehicles', 'license_plate')
                                    ->where(fn ($q) => $q->whereNotNull('license_plate'))
                            )
                            ->nullable(),

                        Forms\Components\TextInput::make('bpkb_number')
                            ->label(('tables.bpkb_number'))
                            ->maxLength(255)
                            ->rule(
                                Rule::unique('vehicles','bpkb_number')
                                    ->where(fn ($q) => $q->whereNotNull('bpkb_number'))
                            ),

                        Forms\Components\TextInput::make('purchase_price')
                            ->label(('tables.purchase_price'))
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        Forms\Components\TextInput::make('sale_price')
                            ->label(('sale_price'))
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),

                        Forms\Components\TextInput::make('odometer')
                            ->label(('odometer_km'))
                            ->numeric()
                            ->minValue(0)
                            ->default(fn (VehicleRequest $r) => (int) $r->odometer),

                        Forms\Components\Select::make('vehicle_status')
                            ->label(('vehicle_status'))
                            ->options([
                                'hold'      => __('hold'),
                                'available' => __('available'),
                                'in_repair' => __('in_repair'),
                                'sold'      => __('sold'),
                            ])
                            ->default('hold')
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label(('tables.description'))
                            ->rows(3)
                            ->nullable(),

                        Forms\Components\TextInput::make('dp_percentage')
                            ->label(('tables.dp_percentage'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->nullable(),

                        Forms\Components\Textarea::make('engine_specification')
                            ->label(('tables.engine_specification'))
                            ->rows(2)
                            ->nullable(),

                        Forms\Components\Textarea::make('location')
                            ->label(('tables.location'))
                            ->rows(2)
                            ->nullable(),

                        Forms\Components\Textarea::make('notes_vehicle')
                            ->label(('tables.notes_vehicle'))
                            ->rows(2)
                            ->default(fn (VehicleRequest $r) => $r->notes)
                            ->nullable(),

                        Forms\Components\Toggle::make('send_whatsapp')
                            ->label(('send_whatsapp'))
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

                            // 2) tautkan foto ke vehicle
                            foreach ($record->photos as $i => $photo) {
                                $vehicle->photos()->create([
                                    'path'        => $photo->path,
                                    'photo_order' => $i,
                                ]);
                            }

                            // 3) update request (TIDAK dihapus)
                            $record->update([
                                'status'     => 'converted', // tandai sudah diproses
                                'vehicle_id' => $vehicle->id,
                            ]);

                            // 4) kirim WA (opsional)
                            $waSent = false;
                            if (!empty($data['send_whatsapp'])) {
                                $waSent = app(WhatsAppService::class)->sendText(
                                    $record->supplier->phone,
                                    // gunakan string terjemahan dengan placeholder
                                    __('whatsapp_approve_message', [
                                        'name'   => $record->supplier->name,
                                        'brand'  => $record->brand->name,
                                        'model'  => $record->vehicleModel->name,
                                        'year'   => $record->year->year,
                                        'plate'  => $record->license_plate,
                                    ])
                                );
                            }

                            Notification::make()
                                ->title(('request_approved'))
                                ->body(
                                    __('vehicle_created_with_status', [
                                        'id'     => $vehicle->id,
                                        'status' => $vehicle->status,
                                    ]) . ' ' .
                                    ($waSent ? __('tables.whatsapp_sent') : __('tables.whatsapp_not_sent'))
                                )
                                ->success()
                                ->send();
                        });
                    }),

            // ======== REJECT ========
            Action::make('reject')
                ->label(('reject'))
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->visible(fn (VehicleRequest $r) => ! in_array($r->status, ['converted', 'rejected']))
                ->modalHeading(('reject_request'))
                ->modalSubmitActionLabel(('tables.reject'))
                ->form([
                    Forms\Components\Textarea::make('reason')
                        ->label(('reject_reason'))
                        ->rows(3)
                        ->required()
                        ->minLength(5),
                    Forms\Components\Toggle::make('send_whatsapp')
                        ->label(('send_whatsapp'))
                        ->default(true),
                ])
                ->action(function (array $data, VehicleRequest $record) {
                    DB::transaction(function () use ($data, $record) {
                        // 1) Kirim WA (opsional) pakai reason dari form — TIDAK disimpan ke kolom notes
                        $waSent = false;
                        if (!empty($data['send_whatsapp'])) {
                            $waSent = false;
                            if (!empty($data['send_whatsapp'])) {
                                $waSent = app(WhatsAppService::class)->sendText(
                                    $record->supplier->phone,
                                    "Halo {$record->supplier->name},\n".
                                    "Maaf, pengajuan motor Anda ({$record->brand->name} {$record->vehicleModel->name} {$record->year->year}, plat {$record->license_plate}) *DITOLAK*.\n".
                                    "Alasan: {$data['reason']}\n\n".
                                    "Terima kasih telah menghubungi Lampegan Motor."
                                );
                            }
                        }

                        // 2) Update status saja — JANGAN ubah kolom notes
                        $record->update([
                            'status' => 'rejected',
                        ]);

                        // 3) Notifikasi di panel admin
                        Notification::make()
                            ->title(('request_rejected'))
                            ->body(
                                ($waSent ? __('whatsapp_sent') : __('whatsapp_not_sent'))
                                . "\n" . __('reject_reason_colom') . ' ' . $data['reason']
                            )
                            ->danger()
                            ->send();
                    });
                }),
            ])
            ->actionsColumnLabel(('actions'))
            ->actionsAlignment('start')
            ->emptyStateHeading(('tables.no_requests'))
            ->emptyStateDescription(('tables.no_requests_description'))
            ->striped();
    }
}