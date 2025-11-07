<?php

namespace App\Filament\Resources\SalesSummaries\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesSummaryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ComponentsGrid::make(2)->schema([
                    TextInput::make('base_salary')
                        ->label('Gaji Pokok')
                        ->numeric()
                        ->required()
                        ->prefix('Rp')
                        ->placeholder('Masukkan gaji pokok'),

                    TextInput::make('sales_count')
                        ->label('Unit Terjual (dibayar)')
                        ->numeric()
                        ->disabled()
                        ->suffix('unit')
                        ->afterStateHydrated(function ($component, $state, $set, $record) {
                            if (!$record) {
                                $set('sales_count', 0);
                                $set('bonus', 0);
                                return;
                            }

                            $month = $record->month ?? Carbon::now()->month;
                            $year  = $record->year ?? Carbon::now()->year;

                            $count = DB::table('sales')
                                ->where('user_id', $record->user_id ?? $record->id)
                                ->whereNotIn('status', ['cancel'])
                                ->whereIn('result', ['ACC', 'CASH'])
                                ->whereMonth('sale_date', $month)
                                ->whereYear('sale_date', $year)
                                ->count();

                            $set('sales_count', $count);
                            $set('bonus', self::calculateBonus($count));
                        })
                        ->dehydrated(false),

                    TextInput::make('bonus')
                        ->label('Bonus (otomatis / manual)')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0)
                        ->afterStateUpdated(function ($state, $set, $get, $record) {
                            if ($state === null || $state === '') {
                                $month = $record->month ?? Carbon::now()->month;
                                $year  = $record->year ?? Carbon::now()->year;

                                $count = DB::table('sales')
                                    ->where('user_id', $record->user_id ?? $record->id)
                                    ->whereNotIn('status', ['cancel'])
                                    ->whereIn('result', ['ACC', 'CASH'])
                                    ->whereMonth('sale_date', $month)
                                    ->whereYear('sale_date', $year)
                                    ->count();

                                $set('bonus', self::calculateBonus($count));
                            }
                        })
                        ->helperText('Otomatis dari unit terjual, tapi bisa diketik manual. Kosongkan untuk hitung ulang otomatis.'),
                ]),

                Placeholder::make('total_income')
                    ->label('Total Penghasilan')
                    ->content(fn($get) => 'Rp ' . number_format(($get('base_salary') ?? 0) + ($get('bonus') ?? 0), 0, ',', '.')),
            ]);
    }

    private static function calculateBonus(int $salesCount): int
    {
        if ($salesCount <= 0) return 0;
        if ($salesCount < 5) return 150_000 * $salesCount;
        if ($salesCount < 10) return 250_000 * $salesCount;
        if ($salesCount == 10) return (250_000 * 10) + 500_000;
        return (250_000 * 10) + 500_000 + (150_000 * ($salesCount - 10));
    }
}
