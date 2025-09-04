<?php

namespace App\Filament\Resources\Requests\Pages;

use App\Filament\Resources\Requests\RequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRequests extends ListRecords
{
    protected static string $resource = RequestResource::class;

    protected static ?string $breadcrumb = 'List';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Requests';
    }
}
