<?php

namespace App\Filament\Backgrounds;

use Swis\Filament\Backgrounds\Contracts\ProvidesImages;
use Swis\Filament\Backgrounds\Image;

class MotorcycleImages implements ProvidesImages
{
    // All URLs verified HTTP 200
    private const IMAGES = [
        ['url' => 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Dashboard'],
        ['url' => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Sport'],
        ['url' => 'https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Garage'],
        ['url' => 'https://images.unsplash.com/photo-1580310614729-ccd69652491d?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Workshop'],
        ['url' => 'https://images.unsplash.com/photo-1571008887538-b36bb32f4571?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle City'],
        ['url' => 'https://images.unsplash.com/photo-1622185135505-2d795003994a?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Trail'],
        ['url' => 'https://images.unsplash.com/photo-1598228723793-52759bba239c?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Wheel'],
        ['url' => 'https://images.unsplash.com/photo-1547549082-6bc09f2049ae?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Adventure'],
        ['url' => 'https://images.unsplash.com/photo-1580341289255-5b47c98a59dd?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Chrome'],
        ['url' => 'https://images.unsplash.com/photo-1558979158-65a1eaa08691?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Touring'],
        ['url' => 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Helmet'],
        ['url' => 'https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Road'],
        ['url' => 'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Speed'],
        ['url' => 'https://images.unsplash.com/photo-1543946207-39bd91e70ca7?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Nature'],
        ['url' => 'https://images.unsplash.com/photo-1601758003122-53c40e686a19?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Night'],
        ['url' => 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Classic'],
        ['url' => 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Journey'],
        ['url' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Mountain'],
    ];

    public static function make(): static
    {
        return app(static::class);
    }

    public function getImage(): Image
    {
        $weekOfYear = (int) date('W');
        $index = $weekOfYear % count(self::IMAGES);
        $image = self::IMAGES[$index];

        return new Image(
            "url(\"{$image['url']}\")",
            $image['credit']
        );
    }
}
