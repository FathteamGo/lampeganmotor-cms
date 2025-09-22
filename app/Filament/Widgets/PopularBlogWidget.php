<?php

namespace App\Filament\Widgets;

use App\Models\PostBlog;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

class PopularBlogWidget extends BaseWidget
{
    protected int|string|array $columnSpan = '1/2';
    protected static ?string $heading = 'Blog yang paling sering dilhat';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(
                PostBlog::query()
                    ->where('is_published', true)
                    ->where('views', '>=', 1) 
                    ->orderByDesc('views')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->limit(40),

                Tables\Columns\TextColumn::make('views')
                    ->label('Jumlah Views')
                    ->sortable(),
            ]);
    }
}
