<?php

namespace App\Exports\Sheets\Concerns;

use Illuminate\Support\Facades\Schema as DbSchema;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait SheetStyling
{
    protected function has(string $table, string $column): bool
    {
        return DbSchema::hasColumn($table, $column);
    }

    protected function applyTableStyles(Worksheet $sheet, int $colCount, int $rowCount, array $headers): void
    {
        // Header range
        $lastCol = Coordinate::stringFromColumnIndex($colCount);
        $headerRange = "A1:{$lastCol}1";

        // Header style
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // Border semua sel
        $allRange = "A1:{$lastCol}{$rowCount}";
        $sheet->getStyle($allRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Zebra rows
        for ($r = 2; $r <= $rowCount; $r++) {
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF9F9F9');
            }
        }

        // Auto-size kolom
        for ($c = 1; $c <= $colCount; $c++) {
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
        }

        // Freeze header + AutoFilter
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($headerRange);

        // Format khusus: tanggal & amount
        $dateIdx = [];
        $amtIdx  = [];
        foreach ($headers as $i => $h) {
            $label = strtoupper($h);
            if (in_array($label, ['DATE','TANGGAL'])) {
                $dateIdx[] = $i + 1;
            }
            if (in_array($label, ['AMOUNT','NOMINAL','TOTAL'])) {
                $amtIdx[] = $i + 1;
            }
        }

        if ($rowCount >= 2) {
            foreach ($dateIdx as $c) {
                $range = $sheet->getCellByColumnAndRow($c, 2)->getCoordinate() . ':' .
                         $sheet->getCellByColumnAndRow($c, $rowCount)->getCoordinate();
                $sheet->getStyle($range)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            }
            foreach ($amtIdx as $c) {
                $range = $sheet->getCellByColumnAndRow($c, 2)->getCoordinate() . ':' .
                         $sheet->getCellByColumnAndRow($c, $rowCount)->getCoordinate();
                $sheet->getStyle($range)->getNumberFormat()->setFormatCode("\"Rp\" #,##0");
                $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
    }
}
