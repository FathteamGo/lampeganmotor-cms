<?php

namespace App\Filament\Resources\CategoryBlogs\Pages;

use App\Filament\Resources\CategoryBlogs\CategoryBlogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoryBlog extends CreateRecord
{
    protected static string $resource = CategoryBlogResource::class;
}
