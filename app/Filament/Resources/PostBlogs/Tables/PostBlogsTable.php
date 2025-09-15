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
                // ID
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                // Judul Blog
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40),

                // Slug
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->limit(50),

                // Cover Image
                ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->disk('public') // ambil dari storage/app/public
                    ->height(60)
                    ->width(100)
                    ->square(),

                // Kategori (relasi)
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),

                // Status publikasi
                BooleanColumn::make('is_published')
                    ->label('Publikasi'),

                // Tanggal dibuat
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),

                // Tanggal diupdate
                TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
