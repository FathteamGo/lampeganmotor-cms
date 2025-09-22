<?php

namespace App\Exports;

use App\Exports\Sheets\SummarySheet;
use App\Exports\Sheets\SalesSheet;
use App\Exports\Sheets\IncomesSheet;
use App\Exports\Sheets\ExpensesSheet;
use App\Exports\Sheets\StnkSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProfitAndLossExport implements WithMultipleSheets
{
    public function __construct(
        protected string $startDate,
        protected string $endDate,
        protected ?string $search = null,
    ) {}

    public function sheets(): array
    {
        return [
            new SummarySheet($this->startDate, $this->endDate),
            new SalesSheet  ($this->startDate, $this->endDate, $this->search),
            new IncomesSheet($this->startDate, $this->endDate, $this->search),
            new ExpensesSheet($this->startDate, $this->endDate, $this->search),
            new StnkSheet($this->startDate, $this->endDate, $this->search),
        ];
    }
}
