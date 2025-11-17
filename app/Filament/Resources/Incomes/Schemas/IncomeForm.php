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

                Select::make('category_id')
                    ->label(__('tables.category_name'))
                    ->options(Category::whereType('income')->pluck('name', 'id'))
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
                                'type' => 'income',
                            ])->id;
                        }
                        return null;
                    }),

                TextInput::make('amount')
                    ->label(__('tables.amount'))
                    ->required()
                    ->prefix('Rp')
                    ->reactive()
                    ->lazy()
                    ->extraInputAttributes([
                        'oninput' => "
                            let n = this.value.replace(/[^0-9]/g,'');
                            this.value = n ? new Intl.NumberFormat('id-ID').format(n) : '';
                        ",
                    ])
                    ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)),

                DatePicker::make('income_date')
                    ->label(__('tables.income_date'))
                    ->required()
                    ->default(now()),

                Select::make('customer_id')
                    ->label(__('tables.customer_name'))
                    ->relationship('customer', 'name')
                    ->nullable()
                    ->searchable(),

                Textarea::make('notes')
                    ->label(__('tables.note'))
                    ->columnSpanFull(),
            ]);
    }
}
