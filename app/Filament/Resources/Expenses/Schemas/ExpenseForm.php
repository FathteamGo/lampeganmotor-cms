<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\Category;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('description')
                    ->label('Deskripsi')
                    ->required(),

                Select::make('category_id')
                    ->label('Kategori')
                    ->options(Category::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                TextInput::make('amount')
                    ->label('Jumlah')
                    ->required()
                    ->numeric(),

                DatePicker::make('expense_date')
                    ->label('Tanggal')
                    ->required(),
            ]);
    }
}
