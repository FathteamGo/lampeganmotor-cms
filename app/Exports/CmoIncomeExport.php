<?php
namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CmoIncomeExport implements FromArray, WithHeadings
{
    protected $cmoId;

    public function __construct($cmoId)
    { 
        $this->cmoId = $cmoId;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Customer',
            'Unit',
            'Nopol',
            'Harga Jual',
            'Fee CMO',
        ];
    }

    public function array(): array
    {
        return Sale::with(['customer', 'vehicle'])
            ->where('user_id', $this->cmoId)
            ->get()
            ->map(function ($sale) {
                return [
                    $sale->sale_date,
                    $sale->customer->name ?? '-',
                    $sale->vehicle->model ?? '-',
                    $sale->vehicle->nopol ?? '-',
                    number_format($sale->sale_price),
                    number_format($sale->cmo_fee),
                ];
            })
            ->toArray();
    }
}
