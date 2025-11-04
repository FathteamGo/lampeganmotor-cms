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
                    ->label(__('tables.description'))
                    ->required(),

                Select::make('category_id')
                    ->label(__('tables.category_name'))
                    ->options(Category::whereType('expense')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->createOptionUsing(function ($data) {
                        if (!empty($data['name'])) {
                            return Category::create([
                                'name' => $data['name'],
                                'type' => 'expense',
                            ])->id;
                        }
                        return null;
                    }),

                TextInput::make('amount')
                    ->label(__('tables.amount'))
                    ->required()
                    ->numeric(),

                DatePicker::make('expense_date')
                    ->label(__('tables.expense_date'))
                    ->required()
                    ->default(now()),
            ]);
    }
}
