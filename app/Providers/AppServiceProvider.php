<?php

namespace App\Providers;

use App\Models\Favicon;
use App\Models\HeroSlide;
use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
         app()->setLocale(session('locale', 'id'));

          Filament::serving(function () {
        $favicon = Favicon::latest()->first(); // ambil model terakhir

        if ($favicon?->path) {
            Filament::registerRenderHook(
                'head.start',
                fn (): string => '<link rel="icon" type="image/png" href="' . asset('storage/' . $favicon->path) . '">'
            );
        }
    });
         
    }

    
    
}
