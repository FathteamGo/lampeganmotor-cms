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
                    ->label('Vehicle')
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
                    ->label('Customer')
                    ->options(
                        Customer::all()->pluck('name', 'id')
                    )
                    ->searchable()
                    ->required(),

                // Tanggal Penjualan
                DatePicker::make('sale_date')
                    ->required(),

                // Harga Jual
                TextInput::make('sale_price')
                    ->required()
                    ->numeric(),

                // Metode Pembayaran
                Select::make('payment_method')
                    ->label('Payment Method')
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

                // Catatan
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
