<?php

namespace App\Filament\Resources\CmoSummaries;

use App\Filament\Resources\CmoSummaries\Pages\ListCmoSummaries;
use App\Filament\Resources\CmoSummaries\Pages\EditCmoSummaries;
use App\Filament\Resources\CmoSummaries\Tables\CmoSummariesTable;
use App\Models\Cmo;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CmoSummariesResource extends Resource
{
    protected static ?string $model = Cmo::class;
    protected static ?string $recordTitleAttribute = 'cmo';

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
        return CmoSummariesTable::configure($table);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Laporan Sales & CMO');
    }

    public static function getNavigationLabel(): string
    {
        return 'Penjualan CMO';
    }

    public static function getLabel(): string
    {
        return 'CMO';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCmoSummaries::route('/'),
            // 'edit'  => EditCmoSummaries::route('/{record}/edit'),
        ];
    }
}
