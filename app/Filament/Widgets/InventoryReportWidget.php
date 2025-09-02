<?php
namespace App\Filament\Widgets;

use App\Models\Vehicle;
use Filament\Widgets\Widget;

class InventoryReportWidget extends Widget
{
    // HARUS static
    protected string $view = 'filament.widgets.inventory-report-widget';

    public $vehicles;

    public function mount(): void
    {
        // Ambil semua kendaraan dengan relasi yang dibutuhkan
        $this->vehicles = Vehicle::with(['vehicleModel.brand', 'type', 'year', 'photos'])
            ->latest()
            ->get();
    }
}
