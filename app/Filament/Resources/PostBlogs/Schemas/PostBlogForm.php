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
               TextInput::make('title')
                ->label('Judul Blog')
                ->required()
                ->maxLength(255)
                ->reactive()
                ->afterStateUpdated(function ($state, $set, $get) {
                    // Hanya generate slug jika slug masih kosong
                    if (! $get('slug')) {
                        $set('slug', Str::slug($state));
                    }
                }),

                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Textarea::make('excerpt')
                    ->label('Ringkasan')
                    ->maxLength(500),

                FileUpload::make('cover_image')
                    ->label('Gambar Cover')
                    ->image()
                    ->directory('posts')
                    ->visibility('public')
                    ->required(),

                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required(),

                Toggle::make('is_published')
                    ->label('Publikasi')
                    ->default(false),

                Textarea::make('content')
                    ->label('Konten')
                    ->required(),
            ]);
    }
}
