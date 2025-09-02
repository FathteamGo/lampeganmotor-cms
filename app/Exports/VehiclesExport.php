<?php

namespace App\Exports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VehiclesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Vehicle::with(['vehicleModel.brand', 'type', 'year'])
            ->get()
            ->map(function ($vehicle) {
                return [
                    'ID' => $vehicle->id,
                    'Brand' => optional($vehicle->vehicleModel->brand)->name,
                    'Model' => optional($vehicle->vehicleModel)->name,
                    'Type' => optional($vehicle->type)->name,
                    'Year' => optional($vehicle->year)->year,
                    'License Plate' => $vehicle->license_plate,
                    'Purchase Price' => $vehicle->purchase_price,
                    'Sale Price' => $vehicle->sale_price,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Brand',
            'Model',
            'Type',
            'Year',
            'License Plate',
            'Purchase Price',
            'Sale Price',
        ];
    }
}
