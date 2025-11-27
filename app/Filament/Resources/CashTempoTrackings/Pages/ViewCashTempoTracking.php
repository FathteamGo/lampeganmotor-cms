<?php

namespace App\Filament\Resources\CashTempoTrackings\Pages;

use App\Filament\Resources\CashTempoTrackings\CashTempoTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCashTempoTracking extends ViewRecord
{
    protected static string $resource = CashTempoTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(static::$resource::getUrl('index')),
        ];
    }

    /**
     * Customize page title
     */
    public function getTitle(): string
    {
        $customer = $this->record->customer?->name ?? 'Unknown';
        return "Detail Cash Tempo - {$customer}";
    }
}