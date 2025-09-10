<?php

namespace App\Filament\Resources\HeroSlides\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class HeroSlidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Gambar')
                    ->getStateUsing(function ($record) {
                        // pastikan $record->image ada
                        return $record->image
                            ? asset('storage/' . $record->image)
                            : url('/images/no-image.png'); // fallback
                    })
                    ->height(80)
                    ->width(120),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subtitle')
                    ->label('Sub Judul')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->actions([]);
    }
}
