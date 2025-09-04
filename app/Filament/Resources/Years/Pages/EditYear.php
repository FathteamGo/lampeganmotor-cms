<?php

namespace App\Filament\Resources\Years\Pages;

use App\Filament\Resources\Years\YearResource;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditYear extends EditRecord
{
    protected static string $resource = YearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => Filament::auth()->user()?->role === 'owner'),
        ];
    }
}
