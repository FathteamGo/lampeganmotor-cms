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
                DatePicker::make('sale_date')
                    ->label(__('tables.sale_date'))
                    ->required(),
                TextInput::make('sale_price')
                    ->label(__('tables.sale_price'))
                    ->numeric()
                    ->required(),
                Select::make('payment_method')
                    ->label(__('tables.payment_method'))
                    ->options([
                        'cash'        => 'Cash',
                        'credit'      => 'Credit',
                        'tukartambah' => 'Tukar Tambah',
                        'cash_tempo'  => 'Cash Tempo',
                    ])
                    ->default('cash')
                    ->required(),
                // Sisa Pembayaran (hanya muncul saat cash tempo)
                TextInput::make('remaining_payment')
                    ->label('Sisa Pembayaran')
                    ->numeric()
                    ->visible(fn ($get) => $get('payment_method') === 'cashsale_tempo'),

                // Tanggal Jatuh Tempo (hanya muncul saat cash tempo)
                DatePicker::make('due_date')
                    ->label('Tanggal Jatuh Tempo')
                    ->visible(fn ($get) => $get('payment_method') === 'cash_tempo'),
                Textarea::make('notes')
                    ->label(__('tables.note'))
                    ->columnSpanFull(),
            ]);
    }
}
