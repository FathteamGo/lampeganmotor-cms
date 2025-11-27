<?php

namespace App\Filament\Resources\CashTempoTrackings;

use App\Filament\Resources\CashTempoTrackings\Pages\ListCashTempoTrackings;
use App\Filament\Resources\CashTempoTrackings\Pages\ViewCashTempoTracking;
use App\Filament\Resources\CashTempoTrackings\Schemas\CashTempoTrackingsInfolist;
use App\Filament\Resources\CashTempoTrackings\Tables\CashTempoTrackingsTable;
use App\Models\Sale;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CashTempoTrackingResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $recordTitleAttribute = 'customer.name';

    /** Group Navigasi */
    public static function getNavigationGroup(): ?string
    {
        return __('navigation.transactions');
    }

    /** Label di Sidebar */
    public static function getNavigationLabel(): string
    {
        return __('Cash Tempo Tracking');
    }

    /** Label Jamak */
    public static function getPluralLabel(): string
    {
        return __('Cash Tempo Tracking');
    }

    /** Label Tunggal */
    public static function getLabel(): string
    {
        return __('Cash Tempo Tracking');
    }

    /** Urutan */
    public static function getNavigationSort(): ?int
    {
        return 5;
    }

    // /** Icon */
    // public static function getNavigationIcon(): ?string
    // {
    //     return 'heroicon-o-clock';
    // }

    /** 
     * AUTO FILTER: Cuma tampilkan yang cash_tempo & belum lunas
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['customer', 'vehicle.vehicleModel', 'vehicle.color', 'vehicle.type', 'vehicle.year', 'user'])
            ->where('payment_method', 'cash_tempo')
            ->where('status', '!=', 'cancel')
            ->where('remaining_payment', '>', 0);
    }

    /**
     * Badge di Navigation (Total cash tempo aktif)
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('payment_method', 'cash_tempo')
            ->where('status', '!=', 'cancel')
            ->where('remaining_payment', '>', 0)
            ->count();
    }

    /**
     * Warna badge (merah kalau ada yang jatuh tempo)
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $jatuhTempo = static::getModel()::where('payment_method', 'cash_tempo')
            ->where('status', '!=', 'cancel')
            ->where('remaining_payment', '>', 0)
            ->whereNotNull('due_date')
            ->where('due_date', '<=', now())
            ->exists();

        return $jatuhTempo ? 'danger' : 'warning';
    }

    /**
     * Infolist untuk View Detail (Filament v4.0.3)
     */
    public static function infolist(Schema $schema): Schema
    {
        return CashTempoTrackingsInfolist::configure($schema);
    }

    /**
     * Table Configuration
     */
    public static function table(Table $table): Table
    {
        return CashTempoTrackingsTable::configure($table);
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
            'index' => ListCashTempoTrackings::route('/'),
            'view' => ViewCashTempoTracking::route('/{record}'),
        ];
    }

    /**
     * Nonaktifkan Create & Edit (karena data dari Sales)
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    /**
     * Bisa delete kalau status cancel atau sudah lunas
     */
    public static function canDelete($record): bool
    {
        return $record->status === 'cancel' || $record->remaining_payment <= 0;
    }
}