<?php

namespace App\Filament\Resources\CategoryBlogs;

use App\Filament\Resources\CategoryBlogs\Pages;
use App\Filament\Resources\CategoryBlogs\Schemas\CategoryBlogForm;
use App\Filament\Resources\CategoryBlogs\Tables\CategoryBlogsTable;
use App\Models\CategoriesBlog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CategoryBlogResource extends Resource
{
    protected static ?string $model = CategoriesBlog::class;

    // protected static ?string $navigationIcon = 'heroicon-o-tag'; // biar jelas icon kategori

    public static function getNavigationGroup(): ?string
    {
        return 'Blog';
    }

    public static function form(Schema $schema): Schema
    {
        return CategoryBlogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoryBlogsTable::table($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategoryBlogs::route('/'),
            'create' => Pages\CreateCategoryBlog::route('/create'),
            'edit'   => Pages\EditCategoryBlog::route('/{record}/edit'),
        ];
    }
}
