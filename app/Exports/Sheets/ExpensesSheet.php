<?php

namespace App\Exports\Sheets;

use App\Models\Expense;
use App\Exports\Sheets\Concerns\SheetStyling;
use Illuminate\Support\Carbon;
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
        protected ?string $search = null,
    ) {}

    public function title(): string { return 'Expenses'; }

    public function array(): array
    {
        $headers = ['TANGGAL','NAMA','KATEGORI','TAHUN','KETERANGAN','NOMINAL'];

        $q = Expense::query()
            ->whereBetween('expense_date', [$this->start, $this->end])
            ->orderBy('expense_date');

        if (filled($this->search)) {
            $s = '%'.trim($this->search).'%';
            $q->where(function ($qq) use ($s) {
                $qq->where('description','like',$s)
                   ->orWhere('notes','like',$s)
                   ->orWhere('amount','like',$s)
                   ->orWhere('name','like',$s)
                   ->orWhere('category','like',$s);
            });
        }

        $rows = [];
        foreach ($q->get() as $r) {
            $rows[] = [
                Carbon::parse($r->expense_date ?? $r->created_at)->toDateString(),
                (string) ($r->name ?? $r->title ?? $r->description ?? ''),
                (string) ($r->category ?? ''),
                (string) ($r->year ?? ''),
                (string) ($r->notes ?? $r->description ?? ''),
                (float) ($r->amount ?? 0), // tanpa minus, sama seperti tampilan
            ];
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
