<?php

namespace App\Filament\Resources\Incomes;

use App\Filament\Resources\Incomes\Pages\CreateIncome;
use App\Filament\Resources\Incomes\Pages\EditIncome;
use App\Filament\Resources\Incomes\Pages\ListIncomes;
use App\Filament\Resources\Incomes\Pages\ViewIncome;
use App\Filament\Resources\Incomes\Schemas\IncomeForm;
use App\Filament\Resources\Incomes\Schemas\IncomeInfolist;
use App\Filament\Resources\Incomes\Tables\IncomesTable;
use App\Models\Income;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class IncomeResource extends Resource
{
    protected static ?string $model = Income::class;

    protected static ?string $recordTitleAttribute = 'description';

    /** 🔹 Group Navigasi */
    public static function getNavigationGroup(): ?string
    {
        return __('navigation.financial');
    }

    /** 🔹 Label di Sidebar */
    public static function getNavigationLabel(): string
    {
        return __('navigation.incomes');
    }

    /** 🔹 Label Jamak (List, Index) */
    public static function getPluralLabel(): string
    {
        return __('navigation.incomes');
    }

    /** 🔹 Label Tunggal (Create, Edit, View) */
    public static function getLabel(): string
    {
        return __('navigation.incomes');
    }

    public static function form(Schema $schema): Schema
    {
        return IncomeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return IncomeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IncomesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIncomes::route('/'),
            'create' => CreateIncome::route('/create'),
            'view' => ViewIncome::route('/{record}'),
            'edit' => EditIncome::route('/{record}/edit'),
        ];
    }
}
