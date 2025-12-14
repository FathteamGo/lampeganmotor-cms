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
        return $schema->components([
            ComponentsGrid::make(2)->schema([

                // =========================
                // GAJI POKOK
                // =========================
                TextInput::make('base_salary')
                    ->label('Gaji Pokok')
                    ->prefix('Rp')
                    ->type('text')
                    ->required()
                    ->extraInputAttributes(self::moneyOnInput())
                    ->dehydrateStateUsing(fn ($state) =>
                        $state ? preg_replace('/[^0-9]/', '', $state) : 0
                    ),

                // =========================
                // UNIT TERJUAL
                // =========================
                TextInput::make('sales_count')
                    ->label('Unit Terjual (dibayar)')
                    ->disabled()
                    ->suffix('unit')
                    ->afterStateHydrated(function ($set, $record) {
                        if (!$record) {
                            $set('sales_count', 0);
                            $set('bonus', '0');
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
                        $set('bonus', number_format(
                            self::calculateBonus($count),
                            0,
                            ',',
                            '.'
                        ));
                    })
                    ->dehydrated(false),

                // =========================
                // BONUS
                // =========================
                TextInput::make('bonus')
                    ->label('Bonus')
                    ->prefix('Rp')
                    ->type('text')
                    ->extraInputAttributes(self::moneyOnInput())
                    ->dehydrateStateUsing(fn ($state) =>
                        $state ? preg_replace('/[^0-9]/', '', $state) : 0
                    ),

                // =========================
                // LEMBUR
                // =========================
                TextInput::make('overtime')
                    ->label('Lembur')
                    ->prefix('Rp')
                    ->type('text')
                    ->default('0')
                    ->extraInputAttributes(self::moneyOnInput())
                    ->dehydrateStateUsing(fn ($state) =>
                        $state ? preg_replace('/[^0-9]/', '', $state) : 0
                    ),
            ]),

            // =========================
            // TOTAL PENGHASILAN
            // =========================
            Placeholder::make('total_income')
                ->label('Total Penghasilan')
                ->content(fn ($get) =>
                    'Rp ' . number_format(
                        (int) preg_replace('/[^0-9]/', '', $get('base_salary') ?? 0)
                        + (int) preg_replace('/[^0-9]/', '', $get('bonus') ?? 0)
                        + (int) preg_replace('/[^0-9]/', '', $get('overtime') ?? 0),
                        0,
                        ',',
                        '.'
                    )
                ),
        ]);
    }

    // =========================
    // FORMAT REALTIME (INLINE)
    // =========================
    private static function moneyOnInput(): array
    {
        return [
            'inputmode' => 'numeric',
            'oninput' => "
                const input = this;
                const start = input.selectionStart;
                const oldLength = input.value.length;

                let raw = input.value.replace(/[^0-9]/g, '');
                let formatted = raw
                    ? new Intl.NumberFormat('id-ID').format(raw)
                    : '';

                input.value = formatted;

                const newLength = formatted.length;
                const diff = newLength - oldLength;
                const newPos = Math.max(start + diff, 0);

                input.setSelectionRange(newPos, newPos);
            ",
        ];
    }

    // =========================
    // HITUNG BONUS
    // =========================
    private static function calculateBonus(int $salesCount): int
    {
        if ($salesCount <= 0) return 0;
        if ($salesCount < 5) return 150_000 * $salesCount;
        if ($salesCount < 10) return 250_000 * $salesCount;
        if ($salesCount === 10) return 3_000_000;
        return 3_000_000 + (150_000 * ($salesCount - 10));
    }
}
