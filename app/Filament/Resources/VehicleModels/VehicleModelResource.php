<?php

namespace App\Filament\Resources\VehicleModels;

use App\Filament\Resources\VehicleModels\Pages\CreateVehicleModel;
use App\Filament\Resources\VehicleModels\Pages\EditVehicleModel;
use App\Filament\Resources\VehicleModels\Pages\ListVehicleModels;
use App\Filament\Resources\VehicleModels\Schemas\VehicleModelForm;
use App\Filament\Resources\VehicleModels\Tables\VehicleModelsTable;
use App\Models\VehicleModel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class VehicleModelResource extends Resource
{
    protected static ?string $model = VehicleModel::class;

    protected static ?string $recordTitleAttribute = 'name';

    /** 🔹 Group Navigasi */
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
        return __('navigation.vehicle_models');
    }

    /** 🔹 Label Jamak */
    public static function getPluralLabel(): string
    {
        return __('navigation.vehicle_models');
    }

    /** 🔹 Label Tunggal */
    public static function getLabel(): string
    {
        return __('navigation.vehicle_models');
    }

    public static function form(Schema $schema): Schema
    {
        return VehicleModelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehicleModelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListVehicleModels::route('/'),
            'create' => CreateVehicleModel::route('/create'),
            'edit'   => EditVehicleModel::route('/{record}/edit'),
        ];
    }
}
