<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\Cmo;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CmoReportExport implements WithMultipleSheets
{
    public function __construct(
        protected Cmo $cmo
    ) {}

    public function sheets(): array
    {
        return [
            new Sheets\CmoRecapSheet($this->cmo),
            new Sheets\CmoDetailSheet($this->cmo),
        ];
    }
}
