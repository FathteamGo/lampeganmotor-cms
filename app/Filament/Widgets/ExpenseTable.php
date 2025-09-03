<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class ExpenseTable extends BaseWidget
{
    protected static ?string $heading = 'Expense';

    public ?string $dateStart = null;
    public ?string $dateEnd   = null;
    public ?string $search    = null;   // <-- NEW

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Expense::query()
                    ->with(['category'])
                    ->when($this->dateStart, fn ($q) => $q->whereDate('expense_date', '>=', $this->dateStart))
                    ->when($this->dateEnd,   fn ($q) => $q->whereDate('expense_date', '<=', $this->dateEnd))

                    // GLOBAL SEARCH
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
                    ->label('#')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')->label('Nama'),
                Tables\Columns\TextColumn::make('category_id')
                    ->label('Kategori')
                    ->getStateUsing(fn (Expense $r) => $r->category->name ?? (string) $r->category_id)
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('year')
                    ->label('Tahun')
                    ->getStateUsing(fn (Expense $r) => $r->expense_date ? Carbon::parse($r->expense_date)->year : '')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')->getStateUsing(fn () => '-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('amount')->label('Nominal')->money('IDR')->sortable(),
            ])
            ->paginated(false)
            ->striped();
    }
}
