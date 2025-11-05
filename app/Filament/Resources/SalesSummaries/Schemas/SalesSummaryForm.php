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

                    // === GAJI POKOK ===
                    TextInput::make('base_salary')
                        ->label('Gaji Pokok')
                        ->numeric()
                        ->required()
                        ->prefix('Rp')
                        ->placeholder('Masukkan gaji pokok'),

                    // === UNIT TERJUAL ===
                    TextInput::make('sales_count')
                        ->label('Unit Terjual (dibayar)')
                        ->numeric()
                        ->disabled() // gak bisa diubah manual
                        ->suffix('unit')
                        ->afterStateHydrated(function ($component, $state, $set, $record) {
                            if (!$record) {
                                $set('sales_count', 0);
                                $set('bonus', 0);
                                return;
                            }

                            $now = Carbon::now();

                            $count = DB::table('sales')
                                ->where('user_id', $record->id)
                                ->whereNotIn('status', ['cancel'])
                                ->whereIn('result', ['ACC', 'CASH'])
                                ->whereMonth('sale_date', $now->month)
                                ->whereYear('sale_date', $now->year)
                                ->count();

                            $set('sales_count', $count);
                            // Isi otomatis pertama kali
                            $set('bonus', self::calculateBonus($count));
                        })
                        ->dehydrated(false),

                    // === BONUS ===
                    TextInput::make('bonus')
                        ->label('Bonus (otomatis / manual)')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0)
                        // ->required() // hindari null error
                        ->afterStateUpdated(function ($state, $set, $get, $record) {
                            // kalau bonus dihapus jadi null atau kosong → isi ulang dari hitungan unit
                            if ($state === null || $state === '') {
                                $now = Carbon::now();

                                $count = DB::table('sales')
                                    ->where('user_id', $record->id)
                                    ->whereNotIn('status', ['cancel'])
                                    ->whereIn('result', ['ACC', 'CASH'])
                                    ->whereMonth('sale_date', $now->month)
                                    ->whereYear('sale_date', $now->year)
                                    ->count();

                                $autoBonus = self::calculateBonus($count);
                                $set('bonus', $autoBonus);
                            }
                        })
                        ->helperText('Otomatis dari unit terjual, tapi bisa diketik manual. Kosongkan untuk hitung ulang otomatis.'),
                ]),

                // === TOTAL PENGHASILAN ===
                Placeholder::make('total_income')
                    ->label('Total Penghasilan')
                    ->content(function ($get) {
                        $total = ($get('base_salary') ?? 0) + ($get('bonus') ?? 0);
                        return 'Rp ' . number_format($total, 0, ',', '.');
                    }),
            ]);
    }

    /**
     * Hitung bonus otomatis sesuai aturan.
     */
    private static function calculateBonus(int $salesCount): int
    {
        if ($salesCount <= 0) {
            return 0;
        }

        // 1–4 unit → 150k per unit
        if ($salesCount < 5) {
            return 150_000 * $salesCount;
        }

        // 5–9 unit → 250k per unit
        if ($salesCount < 10) {
            return 250_000 * $salesCount;
        }

        // 10 unit → 250k per unit + 500k total
        if ($salesCount == 10) {
            return (250_000 * 10) + 500_000;
        }

        // Di atas 11 unit → 250k * 10 + 500k + 150k/unit sisanya
        return (250_000 * 10) + 500_000 + (150_000 * ($salesCount - 10));
    }
}
