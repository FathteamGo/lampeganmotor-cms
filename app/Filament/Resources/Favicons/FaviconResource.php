<?php

namespace App\Filament\Resources\Favicons;

use App\Filament\Resources\Favicons\Pages;
use App\Filament\Resources\Favicons\Schemas\FaviconForm;
use App\Filament\Resources\Favicons\Tables\FaviconsTable;
use App\Models\Favicon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class FaviconResource extends Resource
{
    protected static ?string $model = Favicon::class;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.settings'); // masuk ke menu Settings
    }

    public static function form(Schema $schema): Schema
    {
        return FaviconForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // panggil konfigurasi table yang udah kamu bikin
        return FaviconsTable::table($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFavicons::route('/'),
            'create' => Pages\CreateFavicon::route('/create'),
            'edit'   => Pages\EditFavicon::route('/{record}/edit'),
        ];
    }
}
