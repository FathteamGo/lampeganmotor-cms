<?php

namespace App\Filament\Resources\PostBlogs\Pages;

use App\Filament\Resources\PostBlogs\PostBlogResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePostBlog extends CreateRecord
{
    protected static string $resource = PostBlogResource::class;
}
