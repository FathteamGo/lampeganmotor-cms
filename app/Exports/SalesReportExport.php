<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Sale::with([
            'customer',
            'vehicle.vehicleModel.brand',
            'vehicle.type',
            'vehicle.color',
            'vehicle.year',
        ])->get();
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Date',
            'Customer',
            'Brand',
            'Type',
            'Model',
            'Color',
            'Year',
            'VIN',
            'License Plate',
            'Sale Price',
            'Payment Method',
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->id,
\Illuminate\Support\Carbon::parse($sale->sale_date)->format('Y-m-d'),            $sale->customer?->name,
            $sale->vehicle?->vehicleModel?->brand?->name,
            $sale->vehicle?->type?->name,
            $sale->vehicle?->vehicleModel?->name,
            $sale->vehicle?->color?->name,
            $sale->vehicle?->year?->year,
            $sale->vehicle?->vin,
            $sale->vehicle?->license_plate,
            $sale->sale_price,
            $sale->payment_method,
        ];
    }
}
