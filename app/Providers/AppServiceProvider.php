<?php

namespace App\Providers;

use App\Models\HeroSlide;
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
    }
    
}
