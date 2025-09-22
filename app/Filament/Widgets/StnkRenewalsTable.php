<?php

namespace App\Filament\Widgets;

use App\Models\StnkRenewal;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class StnkRenewalsTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public ?string $dateStart = null;
    public ?string $dateEnd   = null;
    public ?string $search    = null;

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(function (): Builder {
                return StnkRenewal::query()
                    ->with('customer')
                    ->when($this->dateStart, fn ($q, $date) => $q->whereDate('tgl', '>=', $date))
                    ->when($this->dateEnd, fn ($q, $date) => $q->whereDate('tgl', '<=', $date))
                    ->when($this->search, function ($q, $search) {
                        $q->where('license_plate', 'like', "%{$search}%")
                          ->orWhereHas('customer', fn ($q2) =>
                              $q2->where('name', 'like', "%{$search}%")
                          );
                    });
            })
            ->columns([
                Tables\Columns\TextColumn::make('tgl')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('license_plate')
                    ->label('No. Polisi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_pajak_jasa')
                    ->label('Nilai Transaksi')
                    ->money('idr', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('margin_total')
                    ->label('Margin')
                    ->money('idr', true)
                    ->sortable(),
            ]);
    }
}
