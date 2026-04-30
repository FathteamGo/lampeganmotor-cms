<?php

use App\Models\Vehicle;
use App\Models\Sale;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$inconsistentVehicles = Vehicle::where('status', 'available')
    ->whereHas('activeSale')
    ->get();

echo "Total inconsistent vehicles: " . $inconsistentVehicles->count() . "\n";
foreach ($inconsistentVehicles as $v) {
    echo "ID: {$v->id}, Plate: {$v->license_plate}, Model: " . ($v->vehicleModel->name ?? 'N/A') . "\n";
    $sale = $v->activeSale;
    echo "  Sale ID: {$sale->id}, Sale Status: {$sale->status}\n";
}
