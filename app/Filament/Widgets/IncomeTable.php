<?php

namespace App\Filament\Widgets;

use App\Models\Income;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class IncomeTable extends BaseWidget
{
    protected static ?string $heading = null; // Pakai getHeading()
    protected int|string|array $columnSpan = 'full';

    public ?string $dateStart = null;
    public ?string $dateEnd   = null;
    public ?string $search    = null;

    protected function getHeading(): ?string
    {
        return __('tables.income_table');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Income::query()
                    ->with(['category'])
                    ->when($this->dateStart, fn ($q) => $q->whereDate('income_date', '>=', $this->dateStart))
                    ->when($this->dateEnd,   fn ($q) => $q->whereDate('income_date', '<=', $this->dateEnd))
                    ->when(filled($this->search), function ($q) {
                        $s = '%' . trim($this->search) . '%';
                        $q->where(function ($qq) use ($s) {
                            $qq->where('description', 'like', $s)
                               ->orWhere('notes', 'like', $s)
                               ->orWhere('amount', 'like', $s)
                               ->orWhereHas('category', fn ($x) => $x->where('name', 'like', $s));
                        });
                    })
                    ->latest('income_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('income_date')
                    ->label(__('tables.date'))
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label(__('tables.name')),

                Tables\Columns\TextColumn::make('category_id')
                    ->label(__('tables.category'))
                    ->getStateUsing(fn (Income $r) => $r->category->name ?? (string) $r->category_id)
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('year')
                    ->label(__('tables.year'))
                    ->getStateUsing(fn (Income $r) => $r->income_date ? Carbon::parse($r->income_date)->year : '')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label(__('tables.notes'))
                    ->limit(40)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('amount')
                    ->label(__('tables.amount'))
                    ->money('IDR')
                    ->sortable(),
            ])
            ->paginated(false)
            ->striped();
    }
}
