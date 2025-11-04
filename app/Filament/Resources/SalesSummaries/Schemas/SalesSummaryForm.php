<?php

namespace App\Filament\Resources\SalesSummaries\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Grid;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Illuminate\Support\Facades\DB;

class SalesSummaryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Grid 2 kolom: Gaji Pokok & Unit Terjual
                ComponentsGrid::make(2)->schema([
                    TextInput::make('base_salary')
                        ->label('Gaji Pokok')
                        ->numeric()
                        ->required()
                        ->prefix('Rp')
                        ->placeholder('Masukkan gaji pokok'),

                    TextInput::make('sales_count')
                        ->label('Unit Terjual')
                        ->numeric()
                        ->required()
                        ->suffix('unit')
                        ->default(function ($record) {
                            if ($record) {
                                return DB::table('sales')
                                    ->where('user_id', $record->id)
                                    ->where('status', '!=', 'cancel')
                                    ->count();
                            }
                            return 0;
                        })
                        ->afterStateHydrated(function ($state, $set) {
                            // Bonus otomatis saat form load
                            $bonus = 0;
                            if ($state >= 1) $bonus += 150_000;
                            if ($state >= 5) $bonus += 50_000 * 5;
                            if ($state >= 10) $bonus += 500_000;
                            if ($state > 11) $bonus += 150_000 * ($state - 11);
                            $set('bonus', $bonus);
                        })
                        ->afterStateUpdated(function ($state, $set) {
                            // Bonus otomatis saat ubah unit
                            $bonus = 0;
                            if ($state >= 1) $bonus += 150_000;
                            if ($state >= 5) $bonus += 50_000 * 5;
                            if ($state >= 10) $bonus += 500_000;
                            if ($state > 11) $bonus += 150_000 * ($state - 11);
                            $set('bonus', $bonus);
                        }),

                    TextInput::make('bonus')
                        ->label('Bonus')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(fn($record) => $record?->bonus ?? 0)
                        ->helperText('Bonus otomatis dihitung dari unit terjual, tapi bisa diubah manual.'),
                ]),

                // Total Penghasilan
                Placeholder::make('total_income')
                    ->label('Total Penghasilan')
                    ->content(function ($get) {
                        return 'Rp ' . number_format(($get('base_salary') ?? 0) + ($get('bonus') ?? 0), 0, ',', '.');
                    }),
            ]);
    }
}
