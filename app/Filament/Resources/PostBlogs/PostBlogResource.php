<?php

namespace App\Filament\Resources\PostBlogs;

use App\Filament\Resources\PostBlogs\Pages\CreatePostBlog;
use App\Filament\Resources\PostBlogs\Pages\EditPostBlog;
use App\Filament\Resources\PostBlogs\Pages\ListPostBlogs;
use App\Filament\Resources\PostBlogs\Schemas\PostBlogForm;
use App\Filament\Resources\PostBlogs\Tables\PostBlogsTable;
use App\Models\PostBlog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PostBlogResource extends Resource
{
    protected static ?string $model = PostBlog::class;


     public static function getNavigationGroup(): ?string
    {
        return ('Blog');
    }


    public static function form(Schema $schema): Schema
    {
        return PostBlogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostBlogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostBlogs::route('/'),
            'create' => CreatePostBlog::route('/create'),
            'edit' => EditPostBlog::route('/{record}/edit'),
        ];
    }
}
