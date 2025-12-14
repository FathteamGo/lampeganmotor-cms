<?php

namespace App\Exports;

use App\Models\Cmo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AllCmoSummaryExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Cmo::with('sales')
            ->get()
            ->map(fn ($cmo) => [
                'nama_cmo'       => $cmo->name,
                'total_customer' => $cmo->sales->count(),
                'total_fee'      => $cmo->sales->sum('cmo_fee'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Nama CMO',
            'Total Customer',
            'Total Fee CMO',
        ];
    }
}
