<?php

namespace App\Exports\Sheets;

use App\Models\Sale;
use App\Models\Cmo;
use Maatwebsite\Excel\Concerns\FromArray;

class CmoRecapSheet implements FromArray
{
    public function __construct(
        protected Cmo $cmo
    ) {}

    public function array(): array
    {
        $sales = Sale::where('cmo_id', $this->cmo->id)->get();

        return [
            ['Nama CMO', $this->cmo->name],
            ['Total Transaksi', $sales->count()],
            ['Total Fee CMO', $sales->sum('cmo_fee')],
        ];
    }
}
