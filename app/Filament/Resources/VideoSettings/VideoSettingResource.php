<?php

namespace App\Filament\Resources\VideoSettings;

use App\Filament\Resources\VideoSettings\Pages;
use App\Filament\Resources\VideoSettings\Schemas\VideoSettingForm;
use App\Filament\Resources\VideoSettings\Tables\VideoSettingsTable;
use App\Models\VideoSetting;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class VideoSettingResource extends Resource
{
    protected static ?string $model = VideoSetting::class;

    // protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.settings'); // biar sama kayak yang lain
    }

    public static function form(Schema $schema): Schema
    {
        return VideoSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VideoSettingsTable::table($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVideoSettings::route('/'),
            'create' => Pages\CreateVideoSetting::route('/create'),
            'edit'   => Pages\EditVideoSetting::route('/{record}/edit'),
        ];
    }
}
