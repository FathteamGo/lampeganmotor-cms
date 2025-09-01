<?php
namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PurchaseReportExport implements FromQuery, WithHeadings
{
    public function query()
    {
        return Purchase::query()->with([
            'vehicle',
            'supplier',
            'vehicle.vehicleModel.brand',
            'vehicle.type',
            'vehicle.color',
            'vehicle.year',
        ]);
    }

    public function headings(): array
    {
        return [
            'Invoice',
            'Date',
            'Supplier',
            'Address',
            'Phone',
            'Brand',
            'Type',
            'Model',
            'Color',
            'Year',
            'Total Price',
        ];
    }
}
