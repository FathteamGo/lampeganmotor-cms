<?php

namespace App\Exports\Sheets;

use App\Models\Cmo;
use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CmoDetailSheet implements FromCollection, WithHeadings
{
    public function __construct(
        protected Cmo $cmo
    ) {}

    public function collection()
    {
        return Sale::with('vehicle.vehicleModel', 'vehicle.color')
            ->where('cmo_id', $this->cmo->id)
            ->get()
            ->map(fn ($sale) => [
                'customer' => $sale->customer_name,
                'motor'    => optional($sale->vehicle?->vehicleModel)->name,
                'warna'    => optional($sale->vehicle?->color)->name,
                'nopol'    => $sale->vehicle?->license_plate,
                'tanggal'  => $sale->sale_date,
                'otr'      => $sale->sale_price,
                'fee_cmo'  => $sale->cmo_fee,
            ]);
    }

    public function headings(): array
    {
        return [
            'Customer',
            'Motor',
            'Warna',
            'Nopol',
            'Tanggal',
            'OTR',
            'Fee CMO',
        ];
    }
}
