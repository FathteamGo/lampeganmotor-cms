<?php
namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Dropdown Vehicle ambil dari relasi + accessor name
                Select::make('vehicle_id')
                    ->label('Vehicle')
                    ->options(
                        \App\Models\Vehicle::with('vehicleModel')
                            ->get()
                            ->mapWithKeys(fn($vehicle) => [
                                $vehicle->id => $vehicle->vehicleModel->name ?? 'Unknown',
                            ])
                    )
                    ->searchable()
                    ->required(),

                // Dropdown Supplier ambil nama supplier
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(
                        \App\Models\Supplier::all()
                            ->pluck('name', 'id') // ambil [id => name]
                    )
                    ->searchable()
                    ->required(),

                DatePicker::make('purchase_date')
                    ->required(),

                TextInput::make('total_price')
                    ->numeric()
                    ->required(),

                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
