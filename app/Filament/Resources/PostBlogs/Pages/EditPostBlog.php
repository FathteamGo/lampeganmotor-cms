<?php

namespace App\Filament\Resources\PostBlogs\Pages;

use App\Filament\Resources\PostBlogs\PostBlogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostBlog extends EditRecord
{
    protected static string $resource = PostBlogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
