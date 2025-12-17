<?php

namespace App\Filament\Resources\CmoSummaries\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as ComponentsSection;

class CmoSummariesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /* ===============================
                 * INFORMASI CMO (READ ONLY)
                 * =============================== */
                ComponentsSection::make('Informasi CMO')
                    ->schema([
                        Placeholder::make('name')
                            ->label('Nama CMO')
                            ->content(fn ($record) => $record->name),

                        Placeholder::make('total_transaksi')
                            ->label('Total Transaksi')
                            ->content(fn ($record) =>
                                number_format(
                                    $record->sales()
                                        ->where('status', '!=', 'cancel')
                                        ->count(),
                                    0,
                                    ',',
                                    '.'
                                ) . ' Transaksi'
                            ),
                    ])
                    ->columns(2),

                /* ===============================
                 * EDIT FEE CMO
                 * =============================== */
                ComponentsSection::make('Edit Fee CMO')
                    ->description('Fee diambil dari kolom cmo_fee pada tabel sales')
                    ->schema([
                        Repeater::make('sales')
                            ->relationship()
                            ->schema([

                                Placeholder::make('info')
                                    ->label('Transaksi')
                                    ->content(fn ($record) => 
                                        ($record->sale_date
                                            ? $record->sale_date->translatedFormat('d F Y')
                                            : '-') .
                                        ' | Fee Saat Ini: Rp ' .
                                        number_format($record->cmo_fee ?? 0, 0, ',', '.')
                                    ),

                                TextInput::make('cmo_fee')
                                    ->label('Fee CMO')
                                    ->prefix('Rp')
                                    ->formatStateUsing(fn ($state) =>
                                            number_format((int) $state, 0, ',', '.')
                                        )
                                     ->extraInputAttributes([
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
                                        "
                                    ])
                                    ->dehydrateStateUsing(fn ($state) => $state ? preg_replace('/[^0-9]/', '', $state) : null)
                                    ->required(),

                            ])
                            ->columns(2)
                            ->disableItemCreation()
                            ->disableItemDeletion()
                            ->reorderable(false),
                    ]),
            ]);
    }
}
