<?php
namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\Vehicle;
use App\Models\Supplier;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Vehicle dropdown dengan accessor name
                Select::make('vehicle_id')
                    ->label(__('tables.purchase_model'))
                    ->options(
                        Vehicle::with(['vehicleModel', 'color'])
                            ->get()
                            ->mapWithKeys(fn($vehicle) => [
                                $vehicle->id => sprintf(
                                    '%s | %s | %s',
                                    $vehicle->vehicleModel->name ?? 'Unknown Model',
                                    $vehicle->color->name ?? 'Unknown Color',
                                    $vehicle->license_plate ?? 'No Plate'
                                ),
                            ])
                    )
                    ->searchable()
                    ->required(),

                // Supplier dropdown
                Select::make('supplier_id')
                    ->label(__('tables.purchase_supplier'))
                    ->options(Supplier::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                // Tanggal pembelian
                DatePicker::make('purchase_date')
                    ->label(__('tables.purchase_date'))
                    ->required(),

                // Total harga
                TextInput::make('total_price')
                    ->label(__('tables.purchase_total_price'))
                    ->numeric()
                    ->required(),

                // Catatan tambahan
                Textarea::make('notes')
                    ->label(__('tables.note'))
                    ->columnSpanFull(),
            ]);
    }
}
