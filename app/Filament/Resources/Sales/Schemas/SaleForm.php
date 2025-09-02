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
                        Vehicle::with('vehicleModel')
                            ->get()
                            ->mapWithKeys(fn($vehicle) => [
                                $vehicle->id => $vehicle->vehicleModel->name ?? 'Unknown',
                            ])
                    )
                    ->searchable()
                    ->required(),

                // Dropdown Customer
                Select::make('customer_id')
                    ->label('Customer')
                    ->options(
                        Customer::all()->pluck('name', 'id') // [id => name]
                    )
                    ->searchable()
                    ->required(),
                Select::make('vehicle_id')
                ->label('Vehicle')
                ->options(
                    \App\Models\Vehicle::with(['vehicleModel', 'color'])
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


                Select::make('customer_id')
                ->label('Customer')
                ->options(
                    \App\Models\Customer::all()
                        ->pluck('name', 'id')
                )
                ->searchable()
                ->required(),

                DatePicker::make('sale_date')
                    ->required(),

                TextInput::make('sale_price')
                    ->required()
                    ->numeric(),

                Select::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'cash'     => 'Cash',
                        'credit'   => 'Credit',
                        'transfer' => 'Transfer',
                    ])
                    ->default('cash')
                    ->required(),

                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
