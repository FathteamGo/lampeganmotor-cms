<?php

namespace App\Filament\Resources\Favicons\Pages;

use App\Filament\Resources\Favicons\FaviconResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFavicon extends CreateRecord
{
    protected static string $resource = FaviconResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // hapus favicon lama supaya hanya 1 aktif
        \App\Models\Favicon::query()->delete();
        return $data;
    }
}
