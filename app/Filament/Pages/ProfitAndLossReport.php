<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Navigation\NavigationGroup;
use UnitEnum;

class ProfitAndLossReport extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Report & Audit';
    protected static ?int $navigationSort = 5;
    protected string $view = 'filament.pages.profit-and-loss-report';
}
