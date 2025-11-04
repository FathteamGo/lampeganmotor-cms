<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSale extends ViewRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('invoice_cash')
                    ->label('Generate Invoice (Cash)')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn ($record) => $record->payment_method === 'cash')
                    ->url(fn ($record) => route('sales.invoice.cash', $record))
                    ->openUrlInNewTab(),
        ];
    }
}
