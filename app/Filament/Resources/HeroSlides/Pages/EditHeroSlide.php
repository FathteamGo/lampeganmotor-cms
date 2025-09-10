<?php

namespace App\Filament\Resources\HeroSlides\Pages;

use App\Filament\Resources\HeroSlides\HeroSlideResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditHeroSlide extends EditRecord
{
    protected static string $resource = HeroSlideResource::class;

     protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
}
