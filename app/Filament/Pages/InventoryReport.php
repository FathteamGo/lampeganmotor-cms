<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Navigation\NavigationGroup;
use UnitEnum;

class InventoryReport extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Report & Audit';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.pages.inventory-report';
}
