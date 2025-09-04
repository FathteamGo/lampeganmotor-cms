<?php

namespace App\Filament\Resources\Incomes\Schemas;

use App\Models\Category;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class IncomeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('description')
                    ->label(__('tables.description'))
                    ->required()
                    ->maxLength(255),

                // Relasi ke Category
                Select::make('category_id')
                    ->label(__('tables.category_name'))
                    ->options(Category::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                TextInput::make('amount')
                    ->label(__('tables.amount'))
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),

                DatePicker::make('income_date')
                    ->label(__('tables.income_date'))
                    ->required(),

                // Relasi ke Customer
                Select::make('customer_id')
                    ->label(__('tables.customer_name'))
                    ->relationship('customer', 'name')
                    ->searchable(),

                Textarea::make('notes')
                    ->label(__('tables.note'))
                    ->columnSpanFull(),
            ]);
    }
}
