<?php

namespace App\Filament\Backgrounds;

use Swis\Filament\Backgrounds\Contracts\ProvidesImages;
use Swis\Filament\Backgrounds\Image;

class MotorcycleImages implements ProvidesImages
{
    private const IMAGES = [
        ['url' => 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Road'],
        ['url' => 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Dashboard'],
        ['url' => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Sport'],
        ['url' => 'https://images.unsplash.com/photo-1558618047-3c8c76a45081?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Close'],
        ['url' => 'https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Garage'],
        ['url' => 'https://images.unsplash.com/photo-1580310614729-ccd69652491d?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Workshop'],
        ['url' => 'https://images.unsplash.com/photo-1591637333184-19aa844564d3?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Night'],
        ['url' => 'https://images.unsplash.com/photo-1525160442909-4c4bf0eb3c69?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Mountain'],
        ['url' => 'https://images.unsplash.com/photo-1571008887538-b36bb32f4571?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle City'],
        ['url' => 'https://images.unsplash.com/photo-1590885356249-b5e4db0e7cff?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Sunset'],
        ['url' => 'https://images.unsplash.com/photo-1622185135505-2d795003994a?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Trail'],
        ['url' => 'https://images.unsplash.com/photo-1564062287727-31c57e02031b?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Rider'],
        ['url' => 'https://images.unsplash.com/photo-1596395819066-5c5f8be80b6c?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Parked'],
        ['url' => 'https://images.unsplash.com/photo-1615172282427-9a57ef2d142f?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Race'],
        ['url' => 'https://images.unsplash.com/photo-1598228723793-52759bba239c?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Wheel'],
        ['url' => 'https://images.unsplash.com/photo-1547549082-6bc09f2049ae?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Adventure'],
        ['url' => 'https://images.unsplash.com/photo-1580341289255-5b47c98a59dd?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Chrome'],
        ['url' => 'https://images.unsplash.com/photo-1591996906080-0c5f8e1b9c4f?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Classic'],
        ['url' => 'https://images.unsplash.com/photo-1558979158-65a1eaa08691?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Touring'],
        ['url' => 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=1920&q=60&auto=format', 'credit' => 'Unsplash - Motorcycle Helmet'],
    ];

    public static function make(): static
    {
        return app(static::class);
    }

    public function getImage(): Image
    {
        $dayOfYear = (int) date('z');
        $index = $dayOfYear % count(self::IMAGES);
        $image = self::IMAGES[$index];

        return new Image(
            "url(\"{$image['url']}\")",
            $image['credit']
        );
    }
}
