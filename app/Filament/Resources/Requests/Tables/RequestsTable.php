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
                    ->with(['supplier', 'brand', 'vehicleModel', 'year', 'photos'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('year.year')
                    ->label(__('tables.year'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('supplier.name')
                    ->label(__('navigation.supplier'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('supplier.phone')
                    ->label(__('tables.phone'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('brand.name')
                    ->label(__('tables.brand'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('vehicleModel.name')
                    ->label(__('tables.model'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('odometer')
                    ->label(__('tables.odometer'))
                    ->sortable(),

                ImageColumn::make('photo_thumb')
                    ->label(__('tables.photo'))
                    ->getStateUsing(function (VehicleRequest $r) {
                        $firstPhoto = $r->photos->first()?->path;
                        return $firstPhoto
                            ? asset('storage/' . $firstPhoto)
                            : url('/images/no-image.png');
                    })
                    ->square()
                    ->height(48)
                    ->width(64)
                    ->extraAttributes([
                        'style' => 'object-fit:cover;',
                    ]),

                TextColumn::make('license_plate')
                    ->label(__('tables.plate'))
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('notes')
                    ->label(__('tables.note'))
                    ->limit(40)
                    ->tooltip(fn ($state) => (string) $state),
            ])
            ->filters([
                SelectFilter::make('brand_id')
                    ->label(__('tables.brand'))
                    ->relationship('brand', 'name'),

                SelectFilter::make('year_id')
                    ->label(__('tables.year'))
                    ->relationship('year', 'year'),
            ])
            ->actions([
                ViewAction::make()
                    ->label(__('tables.view'))
                    ->icon('heroicon-o-eye'),

                Action::make('approve')
                    ->label(__('tables.approve'))
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn (VehicleRequest $r) => !in_array($r->status, ['converted', 'rejected']))
                    ->form([
                        Forms\Components\Select::make('type_id')
                            ->label(__('tables.type'))
                            ->options(fn () => Type::orderBy('name')->pluck('name','id'))
                            ->required(),

                        Forms\Components\Select::make('color_id')
                            ->label(__('tables.color'))
                            ->options(fn () => Color::orderBy('name')->pluck('name','id'))
                            ->required(),

                        Forms\Components\TextInput::make('vin')
                            ->label(__('tables.vin'))
                            ->required()
                            ->maxLength(255)
                            ->rule(Rule::unique('vehicles','vin')),

                        Forms\Components\TextInput::make('engine_number')
                            ->label(__('tables.engine_number'))
                            ->required()
                            ->maxLength(255)
                            ->rule(Rule::unique('vehicles','engine_number')),

                        Forms\Components\TextInput::make('license_plate')
                            ->label(__('tables.license_plate'))
                            ->maxLength(255)
                            ->default(fn (VehicleRequest $r) => $r->license_plate)
                            ->placeholder(fn (VehicleRequest $r) => $r->license_plate)
                            ->rule(
                                Rule::unique('vehicles', 'license_plate')
                                    ->where(fn ($q) => $q->whereNotNull('license_plate'))
                            )
                            ->nullable(),

                        Forms\Components\TextInput::make('bpkb_number')
                            ->label(__('tables.bpkb_number'))
                            ->maxLength(255)
                            ->rule(
                                Rule::unique('vehicles','bpkb_number')
                                    ->where(fn ($q) => $q->whereNotNull('bpkb_number'))
                            ),

                        Forms\Components\TextInput::make('purchase_price')
                            ->label(__('tables.purchase_price'))
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        Forms\Components\TextInput::make('sale_price')
                            ->label(__('tables.sale_price'))
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),

                        Forms\Components\TextInput::make('odometer')
                            ->label(__('tables.odometer'))
                            ->numeric()
                            ->minValue(0)
                            ->default(fn (VehicleRequest $r) => (int)$r->odometer),

                        Forms\Components\Textarea::make('description')
                            ->label(__('tables.description'))
                            ->rows(3)
                            ->nullable(),

                        Forms\Components\TextInput::make('dp_percentage')
                            ->label(__('tables.dp_percentage'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->nullable(),

                        Forms\Components\Textarea::make('engine_specification')
                            ->label(__('tables.engine_specification'))
                            ->rows(2)
                            ->nullable(),

                        Forms\Components\Textarea::make('location')
                            ->label(__('tables.location'))
                            ->rows(2)
                            ->nullable(),

                        Forms\Components\Textarea::make('notes_vehicle')
                            ->label(__('tables.notes'))
                            ->rows(2)
                            ->default(fn (VehicleRequest $r) => $r->notes)
                            ->nullable(),

                        Forms\Components\Toggle::make('send_whatsapp')
                            ->label(__('tables.send_whatsapp'))
                            ->default(true),
                    ])
                    ->action(function (array $data, VehicleRequest $record) {
                        DB::transaction(function () use ($data, $record) {
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
                                'odometer'             => $data['odometer'] ?? ($record->odometer ?? null),
                                'description'          => $data['description'] ?? null,
                                'dp_percentage'        => $data['dp_percentage'] ?? null,
                                'engine_specification' => $data['engine_specification'] ?? null,
                                'location'             => $data['location'] ?? null,
                                'notes'                => $data['notes_vehicle'] ?? ($record->notes ?? null),
                            ]);

                            foreach ($record->photos as $i => $photo) {
                                $vehicle->photos()->create([
                                    'path'        => $photo->path,
                                    'photo_order' => $i,
                                ]);
                            }

                            $record->update([
                                'status'     => 'converted',
                                'vehicle_id' => $vehicle->id,
                            ]);

                            $waSent = false;
                            if (!empty($data['send_whatsapp'])) {
                                $waSent = app(WhatsAppService::class)->sendText(
                                    $record->supplier->phone,
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
                                ->title(__('request_approved'))
                                ->body(
                                    __('vehicle_created', ['id' => $vehicle->id]) . ' ' .
                                    ($waSent ? __('tables.whatsapp_sent') : __('tables.whatsapp_not_sent'))
                                )
                                ->success()
                                ->send();
                        });
                    }),

                Action::make('reject')
                    ->label(__('tables.reject'))
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->visible(fn (VehicleRequest $r) => !in_array($r->status, ['converted', 'rejected']))
                    ->modalHeading(__('reject.reject_request'))
                    ->modalSubmitActionLabel(__('tables.reject'))
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label(__('reject.reject_reason'))
                            ->rows(3)
                            ->required()
                            ->minLength(5),

                        Forms\Components\Toggle::make('send_whatsapp')
                            ->label(__('tables.send_whatsapp'))
                            ->default(true),
                    ])
                    ->action(function (array $data, VehicleRequest $record) {
                        DB::transaction(function () use ($data, $record) {
                            $waSent = false;
                            if (!empty($data['send_whatsapp'])) {
                                $waSent = app(WhatsAppService::class)->sendText(
                                    $record->supplier->phone,
                                    __('whatsapp_reject_message', [
                                        'name'   => $record->supplier->name,
                                        'brand'  => $record->brand->name,
                                        'model'  => $record->vehicleModel->name,
                                        'year'   => $record->year->year,
                                        'plate'  => $record->license_plate,
                                        'reason' => $data['reason'],
                                    ])
                                );
                            }

                            $record->update([
                                'status' => 'rejected',
                            ]);

                            Notification::make()
                                ->title(__('request_rejected'))
                                ->body(
                                    ($waSent ? __('tables.whatsapp_sent') : __('tables.whatsapp_not_sent'))
                                    . "\n" . __('reject_reason_colom') . ' ' . $data['reason']
                                )
                                ->danger()
                                ->send();
                        });
                    }),
            ])
            ->actionsColumnLabel(__('tables.actions'))
            ->actionsAlignment('start')
            ->emptyStateHeading(__('tables.no_requests'))
            ->emptyStateDescription(__('tables.no_requests_description'))
            ->striped();
    }
}
