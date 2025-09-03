<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\Income;
use App\Models\Expense;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ProfitAndLossOneSheetExport implements FromArray, WithEvents, WithTitle
{
    public function __construct(
        protected string $start,
        protected string $end,
        protected ?string $search = null,
    ) {}

    public function array(): array { return [['']]; }
    public function title(): string { return 'Laporan'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e) {
                $s   = $e->sheet->getDelegate();
                $col = 'C';
                $row = 2;

                $row = $this->writeSummary($s, $col, $row) + 2;

                [$h, $r] = $this->dataSales();
                $row = $this->writeDetail($s, $col, $row, 'Sales', $h, $r) + 2;

                [$h, $r] = $this->dataIncomes();
                $row = $this->writeDetail($s, $col, $row, 'Income', $h, $r) + 2;

                [$h, $r] = $this->dataExpenses();
                $this->writeDetail($s, $col, $row, 'Expense', $h, $r);
            },
        ];
    }

    /* ================= DATA ================= */

    protected function dataSales(): array
    {
        $headers = ['TANGGAL','NAMA','KATEGORI','TAHUN','KETERANGAN','NOMINAL','NO INVOICE','TIPE','MODEL','WARNA','METODE'];

        $q = Sale::query()
            ->with(['vehicle.vehicleModel.brand','vehicle.vehicleModel','vehicle.type','vehicle.color','vehicle.year','customer'])
            ->whereBetween('sale_date', [$this->start, $this->end])
            ->orderBy('sale_date');

        if (filled($this->search)) {
            $term = trim($this->search);
            $like = "%{$term}%";
            $num  = preg_replace('/\D+/', '', $term) ?: null;

            $q->where(function ($qq) use ($like, $num) {
                $qq->where('notes', 'like', $like)
                   ->orWhere('sale_price', 'like', $like)
                   ->when($num, fn ($w) => $w->orWhere('id', (int) $num))
                   ->orWhereHas('customer', fn ($x) => $x->where('name', 'like', $like))
                   ->orWhereHas('vehicle.vehicleModel', fn ($x) => $x->where('name', 'like', $like))
                   ->orWhereHas('vehicle.vehicleModel.brand', fn ($x) => $x->where('name', 'like', $like));
            });
        }

        $rows = [];
        foreach ($q->get() as $r) {
            $rows[] = [
                Carbon::parse($r->sale_date)->toDateString(),
                (string) optional($r->customer)->name,
                (string) optional(optional(optional($r->vehicle)->vehicleModel)->brand)->name,
                (string) optional(optional($r->vehicle)->year)->year,
                (string) ($r->notes ?? ''),
                (float) ($r->sale_price ?? 0),
                'INV' . str_pad((string) $r->id, 7, '0', STR_PAD_LEFT),
                (string) optional(optional($r->vehicle)->type)->name,
                (string) optional(optional($r->vehicle)->vehicleModel)->name,
                (string) optional(optional($r->vehicle)->color)->name,
                (string) ($r->payment_method ?? ''),
            ];
        }

        return [$headers, $rows];
    }

    protected function dataIncomes(): array
    {
        // ambil nama kategori lewat JOIN, bukan object
        $headers = ['TANGGAL','NAMA','KATEGORI','TAHUN','KETERANGAN','NOMINAL'];

        $q = Income::query()
            ->leftJoin('categories as c', 'incomes.category_id', '=', 'c.id')
            ->select('incomes.*', 'c.name as category_name')
            ->whereBetween('income_date', [$this->start, $this->end])
            ->orderBy('income_date');

        if (filled($this->search)) {
            $s = '%'.trim($this->search).'%';
            $q->where(function ($qq) use ($s) {
                $qq->where('incomes.description','like',$s)
                   ->orWhere('incomes.notes','like',$s)
                   ->orWhere('incomes.amount','like',$s)
                   ->orWhere('c.name','like',$s);
            });
        }

        $rows = [];
        foreach ($q->get() as $r) {
            $rows[] = [
                Carbon::parse($r->income_date ?? $r->created_at)->toDateString(),
                (string) ($r->description ?? ''),
                (string) ($r->category_name ?? ''),
                (string) (Carbon::parse($r->income_date ?? $r->created_at)->year),
                (string) ($r->notes ?? ''),
                (float) ($r->amount ?? 0),
            ];
        }

        return [$headers, $rows];
    }

    protected function dataExpenses(): array
    {
        $headers = ['TANGGAL','NAMA','KATEGORI','TAHUN','NOMINAL'];

        $q = Expense::query()
            ->leftJoin('categories as c', 'expenses.category_id', '=', 'c.id')
            ->select([
                'expenses.id',
                'expenses.expense_date',
                'expenses.description',
                'expenses.amount',
                'c.name as category_name',
            ])
            ->whereBetween('expense_date', [$this->start, $this->end])
            ->orderBy('expense_date');

        if (filled($this->search)) {
            // $s = '%'.trim($this->search).'%';
            $q->where(function ($qq) {
                $qq->where('expenses.description', 'like', )
                ->orWhere('expenses.amount', 'like', )
                ->orWhere('expenses.expense_date', 'like', )
                ->orWhere('c.name', 'like', );
            });
        }

        $rows = [];
        foreach ($q->get() as $r) {
            $rows[] = [
                \Illuminate\Support\Carbon::parse($r->expense_date)->toDateString(),
                (string) ($r->description ?? ''),                                 
                (string) ($r->category_name ?? ''),                             
                (string) (\Illuminate\Support\Carbon::parse($r->expense_date)->year),
                (float)  ($r->amount ?? 0),                                       
            ];
        }

        return [$headers, $rows];
    }


    /* ================= WRITERS ================= */

    protected function writeSummary(Worksheet $sheet, string $startCol, int $startRow): int
    {
        $sales    = (float) Sale::whereBetween('sale_date',    [$this->start, $this->end])->sum('sale_price');
        $incomes  = (float) Income::whereBetween('income_date',[$this->start, $this->end])->sum('amount');
        $expenses = (float) Expense::whereBetween('expense_date',[$this->start, $this->end])->sum('amount');
        $profit   = $sales + $incomes - $expenses;

        $idx  = Coordinate::columnIndexFromString($startCol);
        $c1   = Coordinate::stringFromColumnIndex($idx);
        $c2   = Coordinate::stringFromColumnIndex($idx + 1);

        // Title
        $sheet->mergeCells("{$c1}{$startRow}:{$c2}{$startRow}");
        $sheet->setCellValue("{$c1}{$startRow}", 'Laporan Profit & Loss');
        $sheet->getStyle("{$c1}{$startRow}")->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle("{$c1}{$startRow}:{$c2}{$startRow}")
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Period
        $p = $startRow + 1;
        $sheet->mergeCells("{$c1}{$p}:{$c2}{$p}");
        $sheet->setCellValue("{$c1}{$p}", "Periode: {$this->start} s/d {$this->end}");
        $sheet->getStyle("{$c1}{$p}:{$c2}{$p}")
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header
        $h = $startRow + 3;
        $sheet->setCellValue("{$c1}{$h}", 'ITEM');
        $sheet->setCellValue("{$c2}{$h}", 'NILAI');
        $sheet->getStyle("{$c1}{$h}:{$c2}{$h}")->getFont()->setBold(true);
        $sheet->getStyle("{$c1}{$h}:{$c2}{$h}")
              ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
        $sheet->getStyle("{$c1}{$h}:{$c2}{$h}")
              ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("{$c1}{$h}:{$c2}{$h}")
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Rows
        $rows = [
            ['SALES',   $sales],
            ['INCOME',  $incomes],
            ['EXPENSE', $expenses],
            ['TOTAL',   $profit],
        ];

        $r = $h + 1;
        foreach ($rows as [$label, $val]) {
            $sheet->setCellValue("{$c1}{$r}", $label);
            $sheet->setCellValueExplicit("{$c2}{$r}", (float)$val, DataType::TYPE_NUMERIC);

            $sheet->getStyle("{$c1}{$r}:{$c2}{$r}")
                  ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("{$c2}{$r}")
                  ->getNumberFormat()->setFormatCode("\"Rp\" #,##0;-" . "\"Rp\" #,##0");
            $sheet->getStyle("{$c2}{$r}")
                  ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $r++;
        }

        // Total highlight
        $last = $r - 1;
        $sheet->getStyle("{$c1}{$last}:{$c2}{$last}")->getFont()->setBold(true);
        $sheet->getStyle("{$c1}{$last}:{$c2}{$last}")
              ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEDEDED');

        // Width
        $sheet->getColumnDimension($c1)->setWidth(28);
        $sheet->getColumnDimension($c2)->setWidth(24);

        return $last;
    }

    /**
     * Tabel detail (judul + header + zebra + border + format tanggal & rupiah + wrap text)
     */
    protected function writeDetail(
        Worksheet $sheet,
        string $startCol,
        int $startRow,
        string $title,
        array $headers,
        array $rows
    ): int {
        $startIdx = Coordinate::columnIndexFromString($startCol);
        $cStart   = Coordinate::stringFromColumnIndex($startIdx);
        $cEnd     = Coordinate::stringFromColumnIndex($startIdx + count($headers) - 1);

        // Title
        $sheet->mergeCells("{$cStart}{$startRow}:{$cEnd}{$startRow}");
        $sheet->setCellValue("{$cStart}{$startRow}", $title);
        $sheet->getStyle("{$cStart}{$startRow}")->getFont()->setBold(true)->setSize(12);

        // Header
        $hRow = $startRow + 1;
        foreach ($headers as $i => $h) {
            $col = Coordinate::stringFromColumnIndex($startIdx + $i);
            $sheet->setCellValue("{$col}{$hRow}", strtoupper($h));
        }
        $sheet->getStyle("{$cStart}{$hRow}:{$cEnd}{$hRow}")->getFont()->setBold(true);
        $sheet->getStyle("{$cStart}{$hRow}:{$cEnd}{$hRow}")
              ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
        $sheet->getStyle("{$cStart}{$hRow}:{$cEnd}{$hRow}")
              ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Data rows
        $r = $hRow + 1;
        foreach ($rows as $row) {
            foreach ($row as $i => $val) {
                $cell = Coordinate::stringFromColumnIndex($startIdx + $i) . $r;

                // Tanggal = kolom pertama
                if ($i === 0 && filled($val)) {
                    $sheet->setCellValueExplicit(
                        $cell,
                        ExcelDate::dateTimeToExcel(new \DateTime((string)$val)),
                        DataType::TYPE_NUMERIC
                    );
                    continue;
                }

                // Nominal (cari kolom "NOMINAL")
                if (strtoupper($headers[$i]) === 'NOMINAL') {
                    $sheet->setCellValueExplicit($cell, (float)$val, DataType::TYPE_NUMERIC);
                    continue;
                }

                // Teks biasa
                $sheet->setCellValueExplicit($cell, (string)$val, DataType::TYPE_STRING);
            }
            $r++;
        }
        $last = $r - 1;

        // Border all
        $sheet->getStyle("{$cStart}{$hRow}:{$cEnd}{$last}")
              ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Zebra
        for ($zr = $hRow + 1; $zr <= $last; $zr++) {
            if ($zr % 2 === 0) {
                $sheet->getStyle("{$cStart}{$zr}:{$cEnd}{$zr}")
                      ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF9F9F9');
            }
        }

        // Format tanggal & rupiah
        if ($last >= $hRow + 1) {
            // Tanggal (kolom pertama)
            $dateRange = $sheet->getCellByColumnAndRow($startIdx, $hRow+1)->getCoordinate() . ':' .
                         $sheet->getCellByColumnAndRow($startIdx, $last)->getCoordinate();
            $sheet->getStyle($dateRange)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            $sheet->getStyle($dateRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Nominal
            $nomIdx = array_search('NOMINAL', array_map('strtoupper', $headers), true);
            if ($nomIdx !== false) {
                $nomColIdx = $startIdx + $nomIdx;
                $nomRange  = $sheet->getCellByColumnAndRow($nomColIdx, $hRow+1)->getCoordinate() . ':' .
                             $sheet->getCellByColumnAndRow($nomColIdx, $last)->getCoordinate();
                $sheet->getStyle($nomRange)->getNumberFormat()->setFormatCode("\"Rp\" #,##0;-" . "\"Rp\" #,##0");
                $sheet->getStyle($nomRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }

        // Lebarkan kolom teks + wrap agar tidak kepotong
        $wrapCols = ['NAMA','KATEGORI','KETERANGAN','MODEL','WARNA'];
        foreach ($headers as $i => $h) {
            $colLtr = Coordinate::stringFromColumnIndex($startIdx + $i);
            if (in_array(strtoupper($h), $wrapCols, true)) {
                $sheet->getColumnDimension($colLtr)->setAutoSize(false);
                $sheet->getColumnDimension($colLtr)->setWidth(28); // cukup lebar
                $sheet->getStyle("{$colLtr}".($hRow+1).":{$colLtr}{$last}")
                      ->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            } else {
                $sheet->getColumnDimension($colLtr)->setAutoSize(true);
            }
        }

        // AutoFilter + Freeze
        $sheet->setAutoFilter("{$cStart}{$hRow}:{$cEnd}{$last}");

        return $last;
    }
}
