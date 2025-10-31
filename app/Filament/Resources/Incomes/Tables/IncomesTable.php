<?php

namespace App\Filament\Resources\Incomes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class IncomesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label(__('tables.description'))
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label(__('tables.category'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('amount')
                    ->numeric()
                    ->label(__('tables.amount'))
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('income_date')
                    ->label(__('tables.income_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('tables.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('tables.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                // 🗓️ Filter Bulan
                Filter::make('month')
                    ->label('Bulan')
                    ->form([
                        Select::make('month')
                            ->label('Bulan')
                            ->options([
                                '1' => 'Januari',
                                '2' => 'Februari',
                                '3' => 'Maret',
                                '4' => 'April',
                                '5' => 'Mei',
                                '6' => 'Juni',
                                '7' => 'Juli',
                                '8' => 'Agustus',
                                '9' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ])
                            ->default(date('n')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['month'] ?? null,
                            fn($q, $month) => $q->whereMonth('income_date', $month)
                        );
                    }),

                // 📆 Filter Tahun
                Filter::make('year')
                    ->label('Tahun')
                    ->form([
                        Select::make('year')
                            ->label('Tahun')
                            ->options(function () {
                                $years = range(date('Y'), 2020);
                                return collect($years)->mapWithKeys(fn($y) => [$y => $y])->toArray();
                            })
                            ->default(date('Y')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['year'] ?? null,
                            fn($q, $year) => $q->whereYear('income_date', $year)
                        );
                    }),
            ])

            ->recordActions([
                ViewAction::make()
                    ->label(__('tables.view')),
                EditAction::make()
                    ->label(__('tables.edit')),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('tables.delete')),
                ]),
            ]);
    }
}
