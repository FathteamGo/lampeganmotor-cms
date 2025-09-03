<?php

namespace App\Exports\Sheets;

use App\Models\Expense;
use App\Exports\Sheets\Concerns\SheetStyling;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema as DbSchema;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpensesSheet implements FromArray, WithTitle, WithEvents
{
    use SheetStyling;

    public function __construct(
        protected string $start,
        protected string $end,
    ) {}

    public function title(): string
    {
        return 'Expenses';
    }

    public function array(): array
    {
        $dateCol   = DbSchema::hasColumn('expenses', 'expense_date') ? 'expense_date' : (DbSchema::hasColumn('expenses','created_at') ? 'created_at' : null);
        $amountCol = DbSchema::hasColumn('expenses', 'amount') ? 'amount' : null;

        $opt = array_filter([
            DbSchema::hasColumn('expenses', 'description') ? 'description' : null,
        ]);

        $headers = array_merge(['DATE', 'AMOUNT'], array_map(fn ($c) => strtoupper($c), $opt));

        $rows = [];
        $query = Expense::query()
            ->when($dateCol, fn ($q) => $q->whereDate($dateCol, '>=', $this->start))
            ->when($dateCol, fn ($q) => $q->whereDate($dateCol, '<=', $this->end))
            ->orderBy($dateCol ?? 'id');

        $selectCols = array_filter([$dateCol, $amountCol, ...$opt]);
        $data = $selectCols ? $query->get($selectCols) : $query->get();

        foreach ($data as $r) {
            $row = [];
            $dateVal = $dateCol ? Carbon::parse($r->{$dateCol}) : null;
            $row[] = $dateVal ? $dateVal->toDateString() : '';
            $row[] = $amountCol ? (float) $r->{$amountCol} : 0;
            foreach ($opt as $c) {
                $row[] = (string) ($r->{$c} ?? '');
            }
            $rows[] = $row;
        }

        return [$headers, ...$rows];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowCount = max(1, $sheet->getHighestDataRow());
                $colCount = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
                $this->applyTableStyles($sheet, $colCount, $rowCount, $this->headerValues($sheet));
            }
        ];
    }

    protected function headerValues(Worksheet $sheet): array
    {
        $lastCol = $sheet->getHighestDataColumn();
        $cells = [];
        for ($c = 'A'; $c <= $lastCol; $c++) {
            $cells[] = (string) $sheet->getCell($c.'1')->getValue();
            if ($c === $lastCol) break;
        }
        return $cells;
    }
}
