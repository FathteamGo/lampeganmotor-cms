<?php
namespace App\Filament\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon; 


class PurchaseReportExporter implements FromCollection, WithHeadings
{
    protected $from;
    protected $until;

    public function __construct($from = null, $until = null)
    {
        $this->from  = $from;
        $this->until = $until;
    }

    public function collection()
    {
        $query = Purchase::with([
            'vehicle.vehicleModel.brand',
            'vehicle.type',
            'vehicle.color',
            'vehicle.year',
            'supplier',
        ]);

        if ($this->from) {
            $query->whereDate('purchase_date', '>=', $this->from);
        }

        if ($this->until) {
            $query->whereDate('purchase_date', '<=', $this->until);
        }

        return $query->get()->map(function ($purchase) {
            return [
                'Invoice'     => $purchase->id,
                'Date'        => Carbon::parse($purchase->purchase_date)->format('Y-m-d'),
                'Supplier'    => $purchase->supplier->name ?? '',
                'Address'     => $purchase->supplier->address ?? '',
                'Phone'       => $purchase->supplier->phone ?? '',
                'Brand'       => $purchase->vehicle->vehicleModel->brand->name ?? '',
                'Type'        => $purchase->vehicle->type->name ?? '',
                'Model'       => $purchase->vehicle->vehicleModel->name ?? '',
                'Color'       => $purchase->vehicle->color->name ?? '',
                'Year'        => $purchase->vehicle->year->year ?? '',
                'Total Price' => $purchase->total_price,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Invoice', 'Date', 'Supplier', 'Address', 'Phone',
            'Brand', 'Type', 'Model', 'Color', 'Year', 'Total Price',
        ];
    }
}
