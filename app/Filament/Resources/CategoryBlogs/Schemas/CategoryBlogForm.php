<?php

namespace App\Filament\Resources\CategoryBlogs\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryBlogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            // Nama Kategori
            Forms\Components\TextInput::make('name')
                ->label('Nama Kategori')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, callable $set) {
                    $set('slug', Str::slug($state));
                }),

            // Slug (otomatis, tidak bisa diubah manual)
            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->disabled() // tidak bisa diinput
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
        ]);
    }
}
