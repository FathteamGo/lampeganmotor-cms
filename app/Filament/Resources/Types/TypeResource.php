<?php

namespace App\Filament\Resources\Types;

use App\Filament\Resources\Types\Pages\CreateType;
use App\Filament\Resources\Types\Pages\EditType;
use App\Filament\Resources\Types\Pages\ListTypes;
use App\Filament\Resources\Types\Schemas\TypeForm;
use App\Filament\Resources\Types\Tables\TypesTable;
use App\Models\Type;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TypeResource extends Resource
{
    protected static ?string $model = Type::class;

    protected static ?string $recordTitleAttribute = 'name';

    /** 🔹 Group Navigasi */
    public static function getNavigationGroup(): ?string
    {
        return __('navigation.master_data');
    }

    /** 🔹 Label di Sidebar */
    public static function getNavigationLabel(): string
    {
        return __('navigation.types');
    }

    /** 🔹 Label Jamak */
    public static function getPluralLabel(): string
    {
        return __('navigation.types');
    }

    /** 🔹 Label Tunggal */
    public static function getLabel(): string
    {
        return __('navigation.types');
    }

    public static function form(Schema $schema): Schema
    {
        return TypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTypes::route('/'),
            'create' => CreateType::route('/create'),
            'edit'   => EditType::route('/{record}/edit'),
        ];
    }
}
