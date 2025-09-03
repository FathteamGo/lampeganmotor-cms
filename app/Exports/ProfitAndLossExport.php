<?php

namespace App\Exports;

use App\Exports\Sheets\SummarySheet;
use App\Exports\Sheets\SalesSheet;
use App\Exports\Sheets\IncomesSheet;
use App\Exports\Sheets\ExpensesSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProfitAndLossExport implements WithMultipleSheets
{
    public function __construct(
        protected string $startDate,
        protected string $endDate,
    ) {}

    public function sheets(): array
    {
        return [
            new SummarySheet($this->startDate, $this->endDate),
            new SalesSheet($this->startDate, $this->endDate),
            new IncomesSheet($this->startDate, $this->endDate),
            new ExpensesSheet($this->startDate, $this->endDate),
        ];
    }
}
