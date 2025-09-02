<?php

namespace App\Filament\Resources\Requests\Pages;

use App\Filament\Resources\Requests\RequestResource;
use App\Filament\Resources\Requests\Schemas\RequestInfolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewRequest extends ViewRecord
{
    protected static string $resource = RequestResource::class;

    public function getTitle(): string
    {
        return 'Detail Request';
    }

    public function infolist(Schema $schema): Schema
    {
        return RequestInfolist::configure($schema);
    }
}
