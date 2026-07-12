<?php

namespace App\Providers;

use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\ServiceProvider;

class BackgroundImageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Inject background CSS into Filament head
        \Filament\Facades\Filament::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => $this->getBackgroundStyles()
        );
    }

    private function getBackgroundStyles(): string
    {
        return <<<'HTML'
<style>
/* Daily Automotive Background for Dashboard */
body::before {
    content: '';
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: -9999;
    background: #0f172a;
}

body::after {
    content: '';
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: -9998;
    background: linear-gradient(135deg, rgba(15,23,42,0.92) 0%, rgba(30,41,59,0.88) 50%, rgba(15,23,42,0.93) 100%);
    pointer-events: none;
}

.fi-sidebar,
.fi-topbar {
    background-color: rgba(15, 23, 42, 0.95) !important;
    backdrop-filter: blur(20px) !important;
}

.fi-main {
    background-color: transparent !important;
}

.fi-content {
    background-color: transparent !important;
}

.fi-widget-card,
.fi-card {
    background-color: rgba(30, 41, 59, 0.85) !important;
    backdrop-filter: blur(10px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
}

.fi-section {
    background-color: rgba(30, 41, 59, 0.8) !important;
    backdrop-filter: blur(10px) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
}

.fi-table-row {
    background-color: rgba(30, 41, 59, 0.6) !important;
}

.fi-table-row:hover {
    background-color: rgba(51, 65, 85, 0.6) !important;
}

.fi-modal-window {
    background-color: rgba(15, 23, 42, 0.98) !important;
    backdrop-filter: blur(20px) !important;
}
</style>
<script>
// Daily rotating automotive background for dashboard
(function() {
    const automotiveImages = [
        'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1558618047-3c8c76a45081?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1580310614729-ccd69652491d?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1591637333184-19aa844564d3?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1525160442909-4c4bf0eb3c69?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1571008887538-b36bb32f4571?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1590885356249-b5e4db0e7cff?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1622185135505-2d795003994a?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1564062287727-31c57e02031b?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1596395819066-5c5f8be80b6c?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1615172282427-9a57ef2d142f?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1598228723793-52759bba239c?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1547549082-6bc09f2049ae?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1580341289255-5b47c98a59dd?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1591996906080-0c5f8e1b9c4f?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1558979158-65a1eaa08691?w=1920&q=50&auto=format',
        'https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=1920&q=50&auto=format',
    ];

    function getDayOfYear() {
        const now = new Date();
        const start = new Date(now.getFullYear(), 0, 0);
        return Math.floor((now - start) / (1000 * 60 * 60 * 24));
    }

    function setBackground() {
        const day = getDayOfYear();
        const url = automotiveImages[day % automotiveImages.length];
        const img = new Image();
        img.onload = function() {
            document.body.style.backgroundImage = 'url(' + url + ')';
            document.body.style.backgroundSize = 'cover';
            document.body.style.backgroundPosition = 'center';
            document.body.style.backgroundAttachment = 'fixed';
        };
        img.onerror = function() {
            // Fallback: solid dark background (already set via CSS)
            console.log('Background image failed to load, using fallback');
        };
        img.src = url;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setBackground);
    } else {
        setBackground();
    }
})();
</script>
HTML;
    }
}
