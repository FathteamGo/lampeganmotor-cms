<?php

namespace App\Filament\Resources\Colors;

use App\Filament\Resources\Colors\Pages\CreateColor;
use App\Filament\Resources\Colors\Pages\EditColor;
use App\Filament\Resources\Colors\Pages\ListColors;
use App\Filament\Resources\Colors\Schemas\ColorForm;
use App\Filament\Resources\Colors\Tables\ColorsTable;
use App\Models\Color;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ColorResource extends Resource
{
    protected static ?string $model = Color::class;

    protected static ?string $recordTitleAttribute = 'name';

    /** 🔹 Group Navigasi (multi-bahasa) */
    public static function getNavigationGroup(): ?string
    {
        return __('navigation.master_data');
    }

    public static function shouldRegisterNavigation(): bool
    {
    return false;
    }


    /** 🔹 Label di Sidebar */
    public static function getNavigationLabel(): string
    {
        return __('navigation.colors');
    }

    /** 🔹 Label Jamak (List, Index) */
    public static function getPluralLabel(): string
    {
        return __('navigation.colors');
    }

    /** 🔹 Label Tunggal (Create, Edit) */
    public static function getLabel(): string
    {
        return __('navigation.color');
    }

    public static function form(Schema $schema): Schema
    {
        return ColorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ColorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListColors::route('/'),
            'create' => CreateColor::route('/create'),
            'edit' => EditColor::route('/{record}/edit'),
        ];
    }
}
