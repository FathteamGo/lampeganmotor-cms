<?php
namespace App\Exports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VehiclesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Vehicle::with(['vehicleModel.brand', 'type', 'year', 'color'])
            ->get()
            ->map(function ($v) {
                return [
                    'Display Name'   => $v->displayName ?? '-',
                    'Brand'          => $v->vehicleModel?->brand?->name,
                    'Model'          => $v->vehicleModel?->name,
                    'Type'           => $v->type?->name,
                    'Color'          => $v->color?->name,
                    'Year'           => $v->year?->year,
                    'License Plate'  => $v->license_plate,
                    'VIN'            => $v->vin,
                    'Engine Number'  => $v->engine_number,
                    'BPKB Number'    => $v->bpkb_number,
                    'Purchase Price' => $v->purchase_price,
                    'Sale Price'     => $v->sale_price,
                    'Status'         => $v->status,
                    'Location'       => $v->location,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Display Name', 'Brand', 'Model', 'Type', 'Color',
            'Year', 'License Plate', 'VIN', 'Engine Number', 'BPKB Number',
            'Purchase Price', 'Sale Price', 'Status', 'Location',
        ];
    }
}
