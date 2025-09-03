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
                TextEntry::make('description'),

                // Tampilkan nama kategori, bukan ID
                TextEntry::make('category.name')
                    ->label('Kategori'),

                TextEntry::make('amount')
                    ->numeric()
                    ->label('Jumlah'),

                TextEntry::make('expense_date')
                    ->date()
                    ->label('Tanggal'),

                TextEntry::make('created_at')
                    ->dateTime()
                    ->label('Dibuat'),

                TextEntry::make('updated_at')
                    ->dateTime()
                    ->label('Diupdate'),
            ]);
    }
}
