<?php

namespace App\Filament\Resources\SalesSummaries;

use App\Filament\Resources\SalesSummaries\Pages\ListSalesSummaries;
use App\Filament\Resources\SalesSummaries\Pages\EditSalesSummary;
use App\Filament\Resources\SalesSummaries\Schemas\SalesSummaryForm;
use App\Filament\Resources\SalesSummaries\Tables\SalesSummariesTable;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SalesSummaryResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SalesSummaryForm::configure($schema);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'owner';
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'owner';
    }

    public static function table(Table $table): Table
    {
        return SalesSummariesTable::configure($table);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Laporan Sales');
    }

    public static function getNavigationLabel(): string
    {
        return 'Penjualan Sales';
    }

    public static function getLabel(): string
    {
        return 'Sales';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesSummaries::route('/'),
            'edit' => EditSalesSummary::route('/{record}/edit'),
        ];
    }
}
