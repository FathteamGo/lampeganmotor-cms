<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Carbon;

class PurchaseReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return ($this->query ?? Purchase::query())
            ->with([
                'supplier',
                'vehicle.vehicleModel.brand',
                'vehicle.type',
                'vehicle.color',
                'vehicle.year',
            ])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Date',
            'Supplier',
            'Address',
            'Phone',
            'Brand',
            'Type',
            'Model',
            'Color',
            'Year',
            'VIN',
            'License Plate',
            'Vehicle Status',
            'Total Price',
        ];
    }

    public function map($purchase): array
    {
        return [
            $purchase->id,
            Carbon::parse($purchase->purchase_date)->format('Y-m-d'),
            $purchase->supplier?->name,
            $purchase->supplier?->address,
            $purchase->supplier?->phone,
            $purchase->vehicle?->vehicleModel?->brand?->name,
            $purchase->vehicle?->type?->name,
            $purchase->vehicle?->vehicleModel?->name,
            $purchase->vehicle?->color?->name,
            $purchase->vehicle?->year?->year,
            $purchase->vehicle?->vin,
            $purchase->vehicle?->license_plate,
            $purchase->vehicle?->status,
            $purchase->total_price,
        ];
    }
}
