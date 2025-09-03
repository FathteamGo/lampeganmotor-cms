<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationGroup;

// Import yang ditambahkan
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use App\Http\Middleware\SetLocale;

use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\RevenueChart;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                DashboardStats::class,
                SalesChart::class,
                RevenueChart::class,
            ])
            ->navigationGroups([
    NavigationGroup::make()
        ->label(__('navigation.master_data'))
        ->icon('heroicon-o-rectangle-stack'),

    NavigationGroup::make()
        ->label(__('navigation.user_management'))
        ->icon('heroicon-o-users')
        ->collapsed(),

    NavigationGroup::make()
        ->label(__('navigation.transactions'))
        ->icon('heroicon-o-currency-dollar')
        ->collapsed(),

    NavigationGroup::make()
        ->label(__('navigation.financial'))
        ->icon('heroicon-o-banknotes')
        ->collapsed(),

    NavigationGroup::make()
        ->label(__('navigation.assets_management'))
        ->icon('heroicon-o-archive')
        ->collapsed(),

    NavigationGroup::make()
        ->label(__('navigation.report_audit'))
        ->icon('heroicon-o-document-chart-bar')
        ->collapsed(),
])

            // Tambahkan render hook untuk language switcher
           ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn (): string => view('components.language-switcher')->render()
            )
        
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocale::class, // Tambahkan middleware locale
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
    
    public function boot()
    {
        // Register Livewire component jika belum auto-discovery
        \Livewire\Livewire::component('language-switcher', \App\Livewire\LanguageSwitcher::class);
    }
}