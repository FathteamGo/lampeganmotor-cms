<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\DateFilterWidget::class,
            \App\Filament\Widgets\DashboardStats::class,
        ];
    }
}