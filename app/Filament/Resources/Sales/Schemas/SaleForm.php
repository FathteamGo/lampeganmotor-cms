<?php
namespace App\Filament\Resources\Sales\Schemas;

use App\Models\Customer;
use App\Models\Vehicle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Dropdown Vehicle
                Select::make('vehicle_id')
                    ->label(__('tables.purchase_model')) // Bisa pakai label model juga
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

                // Dropdown Customer
                Select::make('customer_id')
                    ->label(__('tables.customer'))
                    ->options(Customer::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                // Tanggal penjualan
                DatePicker::make('sale_date')
                    ->label(__('tables.sale_date'))
                    ->required(),

                // Harga jual
                TextInput::make('sale_price')
                    ->label(__('tables.sale_price'))
                    ->numeric()
                    ->required(),

                // Metode pembayaran
                Select::make('payment_method')
                    ->label(__('tables.payment_method'))
                    ->options([
                        'cash'     => 'Cash',
                        'credit'   => 'Credit',
                        'transfer' => 'Transfer',
                    ])
                    ->default('cash')
                    ->required(),

                // Catatan tambahan
                Textarea::make('notes')
                    ->label(__('tables.note'))
                    ->columnSpanFull(),
            ]);
    }
}
