<?php

namespace App\Filament\Resources\PostBlogs\Pages;

use App\Filament\Resources\PostBlogs\PostBlogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPostBlogs extends ListRecords
{
    protected static string $resource = PostBlogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
