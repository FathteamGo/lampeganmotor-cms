<?php

namespace App\Filament\Resources\Favicons\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

class FaviconsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
              // Kolom gambar favicon
                ImageColumn::make('path')
                    ->label('Favicon')
                    ->disk('public') // ambil dari storage/app/public
                    ->height(60)
                    ->width(60)
                    ->defaultImageUrl(url('/images/no-image.png')),

                // Kolom tanggal dibuat
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i'),

                // Kolom tanggal update terakhir
                TextColumn::make('updated_at')
                    ->label('Update Terakhir')
                    ->dateTime('d M Y H:i'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
