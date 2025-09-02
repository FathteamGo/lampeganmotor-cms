<?php

namespace App\Filament\Widgets;

use App\Models\Income;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class IncomeTable extends BaseWidget
{
    protected static ?string $heading = 'Income';

    public ?string $dateStart = null;
    public ?string $dateEnd   = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Income::query()
                    ->when($this->dateStart, fn ($q) => $q->whereDate('income_date', '>=', $this->dateStart))
                    ->when($this->dateEnd,   fn ($q) => $q->whereDate('income_date', '<=', $this->dateEnd))
            )
            ->columns([
                Tables\Columns\TextColumn::make('income_date')->label('#')->date('d/m')->sortable(),
                Tables\Columns\TextColumn::make('description')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('category_id')->label('Kategori')->toggleable(),
                Tables\Columns\TextColumn::make('amount')->label('Nominal')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('notes')->label('Keterangan')->limit(24)->toggleable(),
            ])
            ->paginated(false);
    }
}
