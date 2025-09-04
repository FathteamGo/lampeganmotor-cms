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
                TextColumn::make('year.year')->label(__('tables.year'))->sortable()->searchable(),
                TextColumn::make('supplier.name')->label(__('tables.supplier_name'))->sortable()->searchable(),
                TextColumn::make('supplier.phone')->label(__('tables.phone'))->sortable()->searchable(),
                TextColumn::make('brand.name')->label(__('tables.brand'))->sortable()->searchable(),
                TextColumn::make('vehicleModel.name')->label(__('tables.model'))->sortable()->searchable(),
                TextColumn::make('odometer')->label(__('tables.odometer'))->sortable(),
                ImageColumn::make('photo_thumb')
                    ->label(__('tables.photo'))
                    ->getStateUsing(fn (VehicleRequest $r) => $r->photos->first()?->path ? Storage::disk('public')->url($r->photos->first()->path) : null)
                    ->square()
                    ->height(48)
                    ->width(64),
                TextColumn::make('license_plate')->label(__('tables.plate'))->toggleable()->searchable(),
                TextColumn::make('notes')->label(__('tables.note'))->limit(40)->tooltip(fn ($s) => $s),
                TextColumn::make('status')
                    ->label(__('tables.status'))
                    ->badge()
                    ->colors([
                        'warning' => 'hold',
                        'success' => 'converted',
                        'danger'  => 'rejected',
                        'info'    => 'available',
                        'gray'    => 'in_repair',
                        'secondary' => 'sold',
                    ]),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('tables.status'))
                    ->options([
                        'hold'      => __('tables.status_hold'),
                        'available' => __('tables.status_available'),
                        'in_repair' => __('tables.status_in_repair'),
                        'sold'      => __('tables.status_sold'),
                        'converted' => __('tables.status_converted'),
                        'rejected'  => __('tables.status_rejected'),
                    ]),
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
                    ->visible(fn (VehicleRequest $r) => ! in_array($r->status, ['converted', 'rejected']))
                    ->form([
                        Forms\Components\Select::make('type_id')
                            ->label(__('tables.type'))
                            ->options(fn () => Type::orderBy('name')->pluck('name','id'))
                            ->required(),
                        Forms\Components\Select::make('color_id')
                            ->label(__('tables.color'))
                            ->options(fn () => Color::orderBy('name')->pluck('name','id'))
                            ->required(),
                        Forms\Components\TextInput::make('vin')->label(__('tables.vin'))->required()->maxLength(255)->rule(Rule::unique('vehicles','vin')),
                        Forms\Components\TextInput::make('engine_number')->label(__('tables.engine_number'))->required()->maxLength(255)->rule(Rule::unique('vehicles','engine_number')),
                        Forms\Components\TextInput::make('license_plate')
                            ->label(__('tables.license_plate'))
                            ->maxLength(255)
                            ->default(fn (VehicleRequest $r) => $r->license_plate)
                            ->placeholder(fn (VehicleRequest $r) => $r->license_plate)
                            ->rule(
                                Rule::unique('vehicles', 'license_plate')->where(fn ($q) => $q->whereNotNull('license_plate'))
                            )
                            ->nullable(),
                        Forms\Components\TextInput::make('bpkb_number')
                            ->label(__('tables.bpkb_number'))
                            ->maxLength(255)
                            ->rule(
                                Rule::unique('vehicles','bpkb_number')->where(fn ($q) => $q->whereNotNull('bpkb_number'))
                            ),
                        Forms\Components\TextInput::make('purchase_price')->label(__('tables.purchase_price'))->numeric()->minValue(0)->required(),
                        Forms\Components\TextInput::make('sale_price')->label(__('tables.sale_price'))->numeric()->minValue(0)->nullable(),
                        Forms\Components\TextInput::make('odometer')->label(__('tables.odometer_km'))->numeric()->minValue(0)->default(fn (VehicleRequest $r) => (int) $r->odometer),
                        Forms\Components\Select::make('vehicle_status')
                            ->label(__('tables.vehicle_status'))
                            ->options([
                                'hold'      => __('tables.status_hold'),
                                'available' => __('tables.status_available'),
                                'in_repair' => __('tables.status_in_repair'),
                                'sold'      => __('tables.status_sold'),
                            ])
                            ->default('hold')
                            ->required(),
                        Forms\Components\Textarea::make('description')->label(__('tables.description'))->rows(3)->nullable(),
                        Forms\Components\TextInput::make('dp_percentage')->label(__('tables.dp_percentage'))->numeric()->minValue(0)->maxValue(100)->nullable(),
                        Forms\Components\Textarea::make('engine_specification')->label(__('tables.engine_specification'))->rows(2)->nullable(),
                        Forms\Components\Textarea::make('location')->label(__('tables.location'))->rows(2)->nullable(),
                        Forms\Components\Textarea::make('notes_vehicle')->label(__('tables.notes_vehicle'))->rows(2)->default(fn (VehicleRequest $r) => $r->notes)->nullable(),
                        Forms\Components\Toggle::make('send_whatsapp')->label(__('tables.send_whatsapp'))->default(true),
                    ])
                    ->action(function (array $data, VehicleRequest $record) {
                        // logika approve tetap sama
                    }),

                Action::make('reject')
                    ->label(__('tables.reject'))
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->visible(fn (VehicleRequest $r) => ! in_array($r->status, ['converted', 'rejected']))
                    ->modalHeading(__('tables.reject_request'))
                    ->modalSubmitActionLabel(__('tables.reject'))
                    ->form([
                        Forms\Components\Textarea::make('reason')->label(__('tables.reject_reason'))->rows(3)->required()->minLength(5),
                        Forms\Components\Toggle::make('send_whatsapp')->label(__('tables.send_whatsapp'))->default(true),
                    ])
                    ->action(function (array $data, VehicleRequest $record) {
                        // logika reject tetap sama
                    }),
            ])
            ->actionsColumnLabel(__('tables.actions'))
            ->actionsAlignment('start')
            ->emptyStateHeading(__('tables.no_requests'))
            ->emptyStateDescription(__('tables.no_requests_description'))
            ->striped();
    }
}
