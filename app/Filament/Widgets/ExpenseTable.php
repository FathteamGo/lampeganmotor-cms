<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpenseTable extends BaseWidget
{
    protected static ?string $heading = 'Expense';

    public ?string $dateStart = null;
    public ?string $dateEnd   = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Expense::query()
                    ->when($this->dateStart, fn ($q) => $q->whereDate('expense_date', '>=', $this->dateStart))
                    ->when($this->dateEnd,   fn ($q) => $q->whereDate('expense_date', '<=', $this->dateEnd))
            )
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')->label('#')->date('d/m')->sortable(),
                Tables\Columns\TextColumn::make('description')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('category_id')->label('Kategori')->toggleable(),
                Tables\Columns\TextColumn::make('amount')->label('Nominal')->money('IDR')->sortable(),
                // TIDAK ada kolom notes di expenses
            ])
            ->paginated(false);
    }
}
