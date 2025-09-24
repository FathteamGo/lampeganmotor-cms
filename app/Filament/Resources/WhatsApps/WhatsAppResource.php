<?php

namespace App\Filament\Resources\WhatsApps;

use App\Filament\Resources\WhatsApps\Pages\CreateWhatsApp;
use App\Filament\Resources\WhatsApps\Pages\EditWhatsApp;
use App\Filament\Resources\WhatsApps\Pages\ListWhatsApps;
use App\Filament\Resources\WhatsApps\Schemas\WhatsAppForm;
use App\Filament\Resources\WhatsApps\Tables\WhatsAppsTable;
use App\Models\WhatsApp;
use App\Models\WhatsAppNumber;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WhatsAppResource extends Resource
{
    protected static ?string $model = WhatsAppNumber::class;

    

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.settings'); // biar sama kayak yang lain
    }

      public static function getModelLabel(): string
    {
        return 'WhatsApp Number';
    }

    public static function getPluralModelLabel(): string
    {
        return 'WhatsApp Numbers';
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'whatsapp-numbers';
    }
    
    public static function form(Schema $schema): Schema
    {
        return WhatsAppForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WhatsAppsTable::configure($table);
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
            'index' => ListWhatsApps::route('/'),
            'create' => CreateWhatsApp::route('/create'),
            'edit' => EditWhatsApp::route('/{record}/edit'),
        ];
    }
}
