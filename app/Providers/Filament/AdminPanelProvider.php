<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Navigation\NavigationGroup;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Auth;
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
            ->navigationGroups($this->getNavigationGroups())
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn(): string => '<div style="margin-left: 0.5rem; padding-left: 1rem;">' . view('components.language-switcher')->render() . '</div>'
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn(): string => '<style>
                    .fi-topbar-end > div:last-child { margin-left: 0.5rem !important; padding-left: 0.5rem !important; }
                    .fi-topbar-end .fi-btn { margin-right: 0.5rem !important; }
                    .fi-topbar-end { gap: 0rem !important; }
                </style>'
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
                SetLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    private function getNavigationGroups(): array
    {
        $navigationGroups = [
            NavigationGroup::make()
                ->label(fn() => __('navigation.master_data'))
                ->icon('heroicon-o-circle-stack')
                ->collapsed(),

            NavigationGroup::make()
                ->label(fn() => __('navigation.user_management'))
                ->icon('heroicon-o-user-group')
                ->collapsed(),

            NavigationGroup::make()
                ->label(fn() => __('navigation.transactions'))
                ->icon('heroicon-o-currency-dollar')
                ->collapsed(),

            NavigationGroup::make()
                ->label(fn() => __('navigation.financial'))
                ->icon('heroicon-o-banknotes')
                ->collapsed(),

            NavigationGroup::make()
                ->label(fn() => __('navigation.assets_management'))
                ->icon('heroicon-o-archive-box')
                ->collapsed(),

            // Report & Audit - akan di-hide/show di level resource
            NavigationGroup::make()
                ->label(fn() => __('navigation.report_audit'))
                ->icon('heroicon-o-clipboard-document-list')
                ->collapsed(),

            NavigationGroup::make()
                ->label('Blog')
                ->icon('heroicon-o-newspaper')
                ->collapsed(),

            NavigationGroup::make()
                ->label(fn() => __('navigation.settings'))
                ->icon('heroicon-o-cog-6-tooth')
                ->collapsed(),

            NavigationGroup::make()
                ->label(fn() => __('navigation.system'))
                ->icon('heroicon-o-server-stack')
                ->collapsed(),

            NavigationGroup::make()
                ->label(fn() => __('navigation.help_support'))
                ->icon('heroicon-o-question-mark-circle')
                ->collapsed(),
        ];

        return $navigationGroups;
    }
}