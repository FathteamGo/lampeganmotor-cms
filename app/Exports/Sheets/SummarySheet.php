<?php

namespace App\Exports\Sheets;

use App\Models\Expense;
use App\Models\Income;
use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class SummarySheet implements FromArray, WithTitle, WithStyles, WithCustomStartCell
{
    public function __construct(
        protected string $start,
        protected string $end,
        protected string $startCell = 'C2',
    ) {}

    public function title(): string { return 'Summary'; }
    public function startCell(): string { return $this->startCell; }

    public function array(): array
    {
        $sales    = (float) Sale::whereBetween('sale_date',    [$this->start, $this->end])->sum('sale_price');
        $incomes  = (float) Income::whereBetween('income_date',[$this->start, $this->end])->sum('amount');
        $expenses = (float) Expense::whereBetween('expense_date',[$this->start, $this->end])->sum('amount');
        $profit   = $sales + $incomes - $expenses;

        return [
            ['Laporan Profit & Loss'],
            ["Periode: {$this->start} s/d {$this->end}"],
            [''],
            ['ITEM', 'NILAI'],
            ['SALES',   $sales],
            ['INCOME',  $incomes],
            // EXPENSE tanpa minus (nilai positif)
            ['EXPENSE', $expenses],
            // TOTAL angka saja (Excel otomatis kasih tanda minus kalau loss)
            ['TOTAL',   $profit],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        [$colLetters, $row] = $this->splitCell($this->startCell);
        $baseIdx = Coordinate::columnIndexFromString($colLetters);
        $col1    = Coordinate::stringFromColumnIndex($baseIdx);
        $col2    = Coordinate::stringFromColumnIndex($baseIdx + 1);

        $titleRow  = $row;
        $periodRow = $row + 1;
        $headerRow = $row + 3;
        $firstData = $headerRow + 1;
        $last      = (int) $sheet->getHighestDataRow();

        // Title
        $sheet->mergeCells("{$col1}{$titleRow}:{$col2}{$titleRow}");
        $sheet->getStyle("{$col1}{$titleRow}")->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle("{$col1}{$titleRow}:{$col2}{$titleRow}")
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($titleRow)->setRowHeight(26);

        // Periode
        $sheet->mergeCells("{$col1}{$periodRow}:{$col2}{$periodRow}");
        $sheet->getStyle("{$col1}{$periodRow}:{$col2}{$periodRow}")
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Header
        $sheet->getStyle("{$col1}{$headerRow}:{$col2}{$headerRow}")->getFont()->setBold(true);
        $sheet->getStyle("{$col1}{$headerRow}:{$col2}{$headerRow}")
              ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
        $sheet->getStyle("{$col1}{$headerRow}:{$col2}{$headerRow}")
              ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("{$col1}{$headerRow}:{$col2}{$headerRow}")
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Tabel
        $tableRange = "{$col1}{$firstData}:{$col2}{$last}";
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("{$col2}{$firstData}:{$col2}{$last}")
              ->getNumberFormat()->setFormatCode("\"Rp\" #,##0");
        $sheet->getStyle($tableRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // TOTAL highlight
        $sheet->getStyle("{$col1}{$last}:{$col2}{$last}")->getFont()->setBold(true);
        $sheet->getStyle("{$col1}{$last}:{$col2}{$last}")
              ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEDEDED');

        // Lebar kolom
        $sheet->getColumnDimension($col1)->setWidth(28);
        $sheet->getColumnDimension($col2)->setWidth(24);

        return [];
    }

    protected function splitCell(string $cell): array
    {
        if (!preg_match('/^([A-Z]+)(\d+)$/i', $cell, $m)) return ['A', 1];
        return [strtoupper($m[1]), (int) $m[2]];
    }
}
