<?php

namespace App\Filament\Resources\Years;

use App\Filament\Resources\Years\Pages\CreateYear;
use App\Filament\Resources\Years\Pages\EditYear;
use App\Filament\Resources\Years\Pages\ListYears;
use App\Filament\Resources\Years\Schemas\YearForm;
use App\Filament\Resources\Years\Tables\YearsTable;
use App\Models\Year;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class YearResource extends Resource
{
    protected static ?string $model = Year::class;

    protected static ?string $recordTitleAttribute = 'year';

    /** 🔹 Group Navigasi */
    public static function getNavigationGroup(): ?string
    {
        return __('navigation.master_data');
    }

    /** 🔹 Label di Sidebar */
    public static function getNavigationLabel(): string
    {
        return __('navigation.years');
    }

    /** 🔹 Label Jamak */
    public static function getPluralLabel(): string
    {
        return __('navigation.years');
    }

    /** 🔹 Label Tunggal */
    public static function getLabel(): string
    {
        return __('navigation.year');
    }

    public static function form(Schema $schema): Schema
    {
        return YearForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return YearsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListYears::route('/'),
            'create' => CreateYear::route('/create'),
            'edit'   => EditYear::route('/{record}/edit'),
        ];
    }
}
