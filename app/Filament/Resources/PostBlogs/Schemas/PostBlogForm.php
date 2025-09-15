<?php

namespace App\Filament\Resources\PostBlogs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostBlogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Judul Blog
                TextInput::make('title')
                    ->label('Judul Blog')
                    ->required()
                    ->maxLength(255)
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        // Generate slug otomatis dari title
                        $set('slug', Str::slug($state));
                    }),

                // Slug (otomatis, user tidak bisa edit)
                TextInput::make('slug')
                    ->label('Slug')
                    ->disabled()     // biar user tidak bisa ubah
                    ->dehydrated()   // tetap disimpan ke DB
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                // Ringkasan
                Textarea::make('excerpt')
                    ->label('Ringkasan')
                    ->maxLength(500),

                // Cover Image
                FileUpload::make('cover_image')
                    ->label('Gambar Cover')
                    ->image()
                    ->directory('posts')   // disimpan di storage/app/public/posts
                    ->disk('public')       // disk public
                    ->visibility('public') // file bisa diakses via URL
                    ->imagePreviewHeight('200') // preview di form
                    ->required(),

                // Kategori
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required(),

                // Status publikasi
                Toggle::make('is_published')
                    ->label('Publikasi')
                    ->default(false),

                // Konten
                Textarea::make('content')
                    ->label('Konten')
                    ->required(),
            ]);
    }
}
