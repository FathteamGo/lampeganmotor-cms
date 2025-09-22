<?php

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

class PopularVehicleWidget extends BaseWidget
{
    protected int|string|array $columnSpan = '1/2';
    protected static ?string $heading = 'Motor yang paling sering dilihat';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(
                Vehicle::query()
                    ->with('vehicleModel')
                    ->where('views', '>=', 1) 
                    ->orderByDesc('views')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('vehicleModel.name')
                    ->label('Model Motor')
                    ->limit(40),

                Tables\Columns\TextColumn::make('views')
                    ->label('Jumlah Views')
                    ->sortable(),
            ]);
    }
}
