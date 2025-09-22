<?php

namespace App\Filament\Resources\PostBlogs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
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
                    ->afterStateUpdated(function ($state, $set) {
                        $set('slug', Str::slug($state));
                    }),

                TextInput::make('slug')
                    ->label('Slug')
                    ->disabled()
                    ->dehydrated()
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
                    ->disk('public')
                    ->visibility('public')
                    ->imagePreviewHeight('200')
                    ->required(),

                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required(),

                Toggle::make('is_published')
                    ->label('Publikasi')
                    ->default(false),

                RichEditor::make('content')
                    ->label('Konten')
                    ->required()
                    ->toolbarButtons([
                        'bold', 'italic', 'underline', 'strike', 'link',
                        'bulletList', 'orderedList', 'blockquote'
                    ])
                    ->columnSpan('full'),
            ]);
    }
}
