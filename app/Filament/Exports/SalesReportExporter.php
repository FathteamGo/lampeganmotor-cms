<?php
namespace App\Filament\Exports;

use App\Models\Sale;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesReportExporter implements FromCollection, WithHeadings
{
    protected $from;
    protected $until;

    public function __construct($from = null, $until = null)
    {
        $this->from  = $from ? Carbon::parse($from) : null;
        $this->until = $until ? Carbon::parse($until) : null;
    }

    public function collection()
    {
        $query = Sale::with([
            'vehicle.vehicleModel.brand',
            'vehicle.type',
            'vehicle.color',
            'vehicle.year',
            'customer',
        ]);

        if ($this->from) {
            $query->whereDate('sale_date', '>=', $this->from);
        }

        if ($this->until) {
            $query->whereDate('sale_date', '<=', $this->until);
        }

        return $query->get()->map(function ($sale) {
            return [
                'Invoice'        => $sale->id,
                'Date'           => Carbon::parse($sale->sale_date)->format('Y-m-d'),
                'Customer'       => $sale->customer->name ?? '',
                'Brand'          => $sale->vehicle->vehicleModel->brand->name ?? '',
                'Type'           => $sale->vehicle->type->name ?? '',
                'Model'          => $sale->vehicle->vehicleModel->name ?? '',
                'Color'          => $sale->vehicle->color->name ?? '',
                'Year'           => $sale->vehicle->year->year ?? '',
                'VIN'            => $sale->vehicle->vin ?? '',
                'License Plate'  => $sale->vehicle->license_plate ?? '',
                'Status'         => $sale->vehicle->status ?? '',
                'Sale Price'     => $sale->sale_price,
                'Payment Method' => $sale->payment_method,
                'Sale Notes'     => $sale->notes ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Invoice', 'Date', 'Customer', 'Brand', 'Type', 'Model', 'Color', 'Year',
            'VIN', 'License Plate', 'Status', 'Sale Price', 'Payment Method', 'Sale Notes',
        ];
    }
}
