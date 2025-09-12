<?php

namespace App\Filament\Resources\CategoryBlogs\Pages;

use App\Filament\Resources\CategoryBlogs\CategoryBlogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategoryBlogs extends ListRecords
{
    protected static string $resource = CategoryBlogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
