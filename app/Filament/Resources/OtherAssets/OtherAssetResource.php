<?php

namespace App\Filament\Resources\OtherAssets;

use App\Filament\Resources\OtherAssets\Pages\CreateOtherAsset;
use App\Filament\Resources\OtherAssets\Pages\EditOtherAsset;
use App\Filament\Resources\OtherAssets\Pages\ListOtherAssets;
use App\Filament\Resources\OtherAssets\Pages\ViewOtherAsset;
use App\Filament\Resources\OtherAssets\Schemas\OtherAssetForm;
use App\Filament\Resources\OtherAssets\Schemas\OtherAssetInfolist;
use App\Filament\Resources\OtherAssets\Tables\OtherAssetsTable;
use App\Models\OtherAsset;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class OtherAssetResource extends Resource
{
    protected static ?string $model = OtherAsset::class;

    protected static ?string $recordTitleAttribute = 'name';

    /** 🔹 Group Navigasi */
    public static function getNavigationGroup(): ?string
    {
        return __('navigation.assets_management');
    }

    /** 🔹 Label di Sidebar */
    public static function getNavigationLabel(): string
    {
        return __('navigation.other_assets');
    }

    /** 🔹 Label Jamak */
    public static function getPluralLabel(): string
    {
        return __('navigation.other_assets');
    }

    /** 🔹 Label Tunggal */
    public static function getLabel(): string
    {
        return __('navigation.other_assets');
    }

    public static function form(Schema $schema): Schema
    {
        return OtherAssetForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OtherAssetInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OtherAssetsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOtherAssets::route('/'),
            'create' => CreateOtherAsset::route('/create'),
            'view' => ViewOtherAsset::route('/{record}'),
            'edit' => EditOtherAsset::route('/{record}/edit'),
        ];
    }
}
