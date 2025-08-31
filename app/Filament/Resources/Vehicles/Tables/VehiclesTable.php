<?php

namespace App\Filament\Resources\Vehicles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicleModel.name') // Menggunakan relasi vehicleModel dan menampilkan kolom name
                    ->label('Model')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type.name') // Menggunakan relasi type dan menampilkan kolom name
                    ->label('Type')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('color.name') // Menggunakan relasi color dan menampilkan kolom name
                    ->label('Color')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('year.year') // Menggunakan relasi year dan menampilkan kolom year
                    ->label('Year')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('vin')
                    ->searchable(),
                TextColumn::make('engine_number')
                    ->searchable(),
                TextColumn::make('license_plate')
                    ->label('License Plate')
                    ->searchable(),
                TextColumn::make('bpkb_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('purchase_price')
                    ->numeric()
                    ->sortable()
                    ->money('IDR'), // Menampilkan sebagai mata uang
                TextColumn::make('sale_price')
                    ->numeric()
                    ->sortable()
                    ->money('IDR'), // Menampilkan sebagai mata uang
                TextColumn::make('status')
                    ->badge() // Menggunakan badge untuk visualisasi yang lebih baik
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'sold' => 'danger',
                        'in_repair' => 'warning',
                        'hold' => 'gray',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
