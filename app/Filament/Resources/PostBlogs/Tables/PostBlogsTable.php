<?php

namespace App\Filament\Resources\PostBlogs\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BooleanColumn;

class PostBlogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('title')->label('Judul')->searchable(),
                TextColumn::make('slug')->label('Slug')->searchable(),
                ImageColumn::make('cover_image')->label('Cover'),
                TextColumn::make('category.name')->label('Kategori'),
                BooleanColumn::make('is_published')->label('Publikasi'),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y')->sortable(),
                TextColumn::make('updated_at')->label('Diupdate')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
