<?php

namespace App\Filament\Resources\StnkRenewals;

use App\Filament\Resources\StnkRenewals\Pages\CreateStnkRenewal;
use App\Filament\Resources\StnkRenewals\Pages\EditStnkRenewal;
use App\Filament\Resources\StnkRenewals\Pages\ListStnkRenewals;
use App\Filament\Resources\StnkRenewals\Pages\ViewStnkRenewal;
use App\Filament\Resources\StnkRenewals\Schemas\StnkRenewalForm;
use App\Filament\Resources\StnkRenewals\Tables\StnkRenewalsTable;
use App\Models\StnkRenewal;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class StnkRenewalResource extends Resource
{
    protected static ?string $model = StnkRenewal::class;

    /** 🔹 Group Navigasi */
    public static function getNavigationGroup(): ?string
    {
        return __('navigation.transactions');
    }

    /** 🔹 Label di Sidebar */
    public static function getNavigationLabel(): string
    {
        return __('navigation.stnk_renewals');
    }

    /** 🔹 Label Jamak */
    public static function getPluralLabel(): string
    {
        return __('navigation.stnk_renewals');
    }

    /** 🔹 Label Tunggal */
    public static function getLabel(): string
    {
        return __('navigation.stnk_renewals');
    }

    /** 🔹 Urutan Sidebar */
    public static function getNavigationSort(): ?int
    {
        return 4; // urutan di bawah menu Requests
    }

    public static function table(Table $table): Table
    {
        return StnkRenewalsTable::configure($table);
    }

    public static function form(Schema $schema): Schema
    {
        return StnkRenewalForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListStnkRenewals::route('/'),
            'create' => CreateStnkRenewal::route('/create'),
            'edit'   => EditStnkRenewal::route('/{record}/edit'),
        ];
    }
}
