<?php

namespace App\Filament\Resources\Years;

use App\Filament\Resources\Years\Pages\CreateYear;
use App\Filament\Resources\Years\Pages\EditYear;
use App\Filament\Resources\Years\Pages\ListYears;
use App\Filament\Resources\Years\Schemas\YearForm;
use App\Filament\Resources\Years\Tables\YearsTable;
use App\Models\Year;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class YearResource extends Resource
{
    protected static ?string $model = Year::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string | UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?string $recordTitleAttribute = 'year';

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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListYears::route('/'),
            'create' => CreateYear::route('/create'),
            'edit' => EditYear::route('/{record}/edit'),
        ];
    }
}
