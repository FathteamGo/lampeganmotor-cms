<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExpenseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('description')
                    ->label(__('tables.description')),

                // Tampilkan nama kategori, bukan ID
                TextEntry::make('category.name')
                    ->label(__('tables.category')),

                TextEntry::make('amount')
                    ->numeric()
                    ->label(__('tables.amount')),

                TextEntry::make('expense_date')
                    ->date()
                    ->label(__('tables.expense_date')),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->label(__('tables.created_at')),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->label(__('tables.updated_at')),
            ]);
    }
}
