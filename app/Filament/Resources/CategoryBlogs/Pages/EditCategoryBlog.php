<?php

namespace App\Filament\Resources\CategoryBlogs\Pages;

use App\Filament\Resources\CategoryBlogs\CategoryBlogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategoryBlog extends EditRecord
{
    protected static string $resource = CategoryBlogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
