<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Navigation\NavigationGroup;
use UnitEnum;

class AssetReport extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Report & Audit';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.pages.asset-report';
}
