<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class ExpenseTable extends BaseWidget
{
    protected static ?string $heading = null; // Pakai getHeading()
    protected int|string|array $columnSpan = 'full';

    public ?string $dateStart = null;
    public ?string $dateEnd   = null;
    public ?string $search    = null;

    protected function getHeading(): ?string
    {
        return __('tables.expense');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Expense::query()
                    ->with(['category'])
                    ->when($this->dateStart, fn ($q) => $q->whereDate('expense_date', '>=', $this->dateStart))
                    ->when($this->dateEnd,   fn ($q) => $q->whereDate('expense_date', '<=', $this->dateEnd))
                    ->when(filled($this->search), function ($q) {
                        $s = '%' . trim($this->search) . '%';
                        $q->where(function ($qq) use ($s) {
                            $qq->where('description', 'like', $s)
                               ->orWhere('amount', 'like', $s)
                               ->orWhereHas('category', fn ($x) => $x->where('name', 'like', $s));
                        });
                    })
                    ->latest('expense_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')
                    ->label(__('tables.date'))
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('tables.name')),

                Tables\Columns\TextColumn::make('category_id')
                    ->label(__('tables.category'))
                    ->getStateUsing(fn (Expense $r) => $r->category->name ?? (string) $r->category_id)
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('year')
                    ->label(__('tables.year'))
                    ->getStateUsing(fn (Expense $r) => $r->expense_date ? Carbon::parse($r->expense_date)->year : '')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label(__('tables.notes'))
                    ->getStateUsing(fn () => '-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('tables.amount'))
                    ->money('IDR')
                    ->sortable(),
            ])
            ->paginated(false)
            ->striped();
    }
}
