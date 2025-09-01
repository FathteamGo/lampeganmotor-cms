<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Navigation\NavigationGroup;
use UnitEnum;
use App\Models\Vehicle;


class InventoryReport extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Report & Audit';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Inventory Report';
    protected static ?string $title = 'Laporan & Audit Stok';

    protected string $view = 'filament.pages.inventory-report';

    


     public function getVehiclesProperty()
    {
        return Vehicle::with(['vehicleModel.brand', 'type', 'year', 'photos', 'additionalCosts'])
            ->get();
    }
    
}

    

