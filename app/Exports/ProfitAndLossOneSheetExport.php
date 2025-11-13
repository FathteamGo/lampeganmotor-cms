<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\Income;
use App\Models\Expense;
use App\Models\StnkRenewal;
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
                $sheet = $e->sheet->getDelegate();
                $col = 'C';
                $row = 2;

                // Summary (SALES, INCOME, EXPENSE, STNK, LABA BERSIH)
                $row = $this->writeSummary($sheet, $col, $row) + 2;

                // Detail Sales
                [$h, $r] = $this->dataSales();
                $row = $this->writeDetail($sheet, $col, $row, 'Sales', $h, $r, 'sale') + 2;

                // Detail Income
                [$h, $r] = $this->dataIncomes();
                $row = $this->writeDetail($sheet, $col, $row, 'Income', $h, $r) + 2;

                // Detail Expenses
                [$h, $r] = $this->dataExpenses();
                $row = $this->writeDetail($sheet, $col, $row, 'Expense', $h, $r) + 2;

                // Detail STNK Renewal
                [$h, $r] = $this->dataStnk();
                $this->writeDetail($sheet, $col, $row, 'STNK Renewal', $h, $r);
            },
        ];
    }

    /* ================= DATA ================= */

    protected function dataSales(): array
    {
        $headers = ['TANGGAL','NAMA','KATEGORI','TAHUN','KETERANGAN','NOMINAL','NO INVOICE','TIPE','MODEL','WARNA','METODE','STATUS'];

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
                (string) ($r->status ?? ''),
            ];
        }

        return [$headers, $rows];
    }

    protected function dataIncomes(): array
    {
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
            $s = '%'.trim($this->search).'%';
            $q->where(function ($qq) use ($s) {
                $qq->where('expenses.description','like',$s)
                   ->orWhere('expenses.amount','like',$s)
                   ->orWhere('expenses.expense_date','like',$s)
                   ->orWhere('c.name','like',$s);
            });
        }

        $rows = [];
        foreach ($q->get() as $r) {
            $rows[] = [
                Carbon::parse($r->expense_date)->toDateString(),
                (string) ($r->description ?? ''),
                (string) ($r->category_name ?? ''),
                (string) (Carbon::parse($r->expense_date)->year),
                (float)  ($r->amount ?? 0),
            ];
        }

        return [$headers, $rows];
    }

    protected function dataStnk(): array
    {
        $headers = [
            'TANGGAL',
            'NO. POLISI',
            'CUSTOMER',
            'JENIS PEKERJAAN',
            'VENDOR',
            'PEMBAYARAN KE VENDOR',
            'TOTAL BAYAR',
            'KE SAMSAT',
            'MARGIN',
        ];

        $q = StnkRenewal::query()
            ->with('customer')
            ->whereBetween('tgl', [$this->start, $this->end])
            ->orderBy('tgl');

        if (filled($this->search)) {
            $s = '%'.trim($this->search).'%';
            $q->where(function ($qq) use ($s) {
                $qq->where('license_plate', 'like', $s)
                   ->orWhereHas('customer', fn($w) => $w->where('name', 'like', $s))
                   ->orWhere('vendor', 'like', $s)
                   ->orWhere('jenis_pekerjaan', 'like', $s);
            });
        }

        $rows = [];
        foreach ($q->get() as $r) {
            $rows[] = [
                Carbon::parse($r->tgl)->toDateString(),
                (string) $r->license_plate,
                (string) optional($r->customer)->name,
                (string) ($r->jenis_pekerjaan ?? '-'),
                (string) ($r->vendor ?? '-'),
                (float) ($r->payvendor ?? 0),
                (float) ($r->total_pajak_jasa ?? 0),
                (float) ($r->pembayaran_ke_samsat ?? 0),
                (float) ($r->margin_total ?? 0),
            ];
        }

        return [$headers, $rows];
    }

    /* ================= WRITERS ================= */

    protected function writeSummary(Worksheet $sheet, string $startCol, int $startRow): int
    {
        $sales    = (float) Sale::whereBetween('sale_date', [$this->start, $this->end])
                                 ->where('status', '!=', 'cancel')
                                 ->sum('sale_price');
        $incomes  = (float) Income::whereBetween('income_date',[$this->start, $this->end])->sum('amount');
        $expenses = (float) Expense::whereBetween('expense_date',[$this->start, $this->end])->sum('amount');

        $stnkIncome  = (float) StnkRenewal::whereBetween('tgl', [$this->start, $this->end])->sum('margin_total');
        $stnkExpense = (float) StnkRenewal::whereBetween('tgl', [$this->start, $this->end])
            ->get()
            ->sum(fn($r) => $r->payvendor + $r->pembayaran_ke_samsat);

        $profit   = $sales + $incomes + $stnkIncome - ($expenses + $stnkExpense);

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
            ['SALES',       $sales],
            ['INCOME',      $incomes],
            ['STNK INCOME', $stnkIncome],
            ['EXPENSE',     $expenses],
            ['STNK EXPENSE', $stnkExpense],
        ];

        $r = $h + 1;
        foreach ($rows as [$label, $val]) {
            $sheet->setCellValue("{$c1}{$r}", $label);
            $sheet->setCellValueExplicit("{$c2}{$r}", (float)$val, DataType::TYPE_NUMERIC);
            $sheet->getStyle("{$c1}{$r}:{$c2}{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("{$c2}{$r}")->getNumberFormat()->setFormatCode("\"Rp\" #,##0;-"."\"Rp\" #,##0");
            $sheet->getStyle("{$c2}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $r++;
        }

        // LABA BERSIH / PROFIT
        $sheet->setCellValue("{$c1}{$r}", 'LABA BERSIH / PROFIT');
        $sheet->setCellValueExplicit("{$c2}{$r}", (float)$profit, DataType::TYPE_NUMERIC);

        // Warna otomatis
        $color = $profit >= 0 ? 'FF008000' : 'FFFF0000';
        $sheet->getStyle("{$c1}{$r}:{$c2}{$r}")->getFont()->setBold(true)->getColor()->setARGB($color);

        $sheet->getStyle("{$c1}{$r}:{$c2}{$r}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("{$c2}{$r}")->getNumberFormat()->setFormatCode("\"Rp\" #,##0;-"."\"Rp\" #,##0");
        $sheet->getStyle("{$c2}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Width
        $sheet->getColumnDimension($c1)->setWidth(28);
        $sheet->getColumnDimension($c2)->setWidth(24);

        return $r;
    }
    
    protected function writeDetail(
        Worksheet $sheet,
        string $startCol,
        int $startRow,
        string $title,
        array $headers,
        array $rows,
        string $type = ''
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
            $isCancel = $type === 'sale' && isset($row[11]) && strtolower($row[11]) === 'cancel';
            foreach ($row as $i => $val) {
                $cell = Coordinate::stringFromColumnIndex($startIdx + $i) . $r;

                // Tanggal
                if ($i === 0 && filled($val)) {
                    $sheet->setCellValueExplicit(
                        $cell,
                        ExcelDate::dateTimeToExcel(new \DateTime((string)$val)),
                        DataType::TYPE_NUMERIC
                    );
                    continue;
                }

                // Nominal / total bayar / margin / ke samsat
                if (strtoupper($headers[$i]) === 'NOMINAL' ||
                    strtoupper($headers[$i]) === 'TOTAL BAYAR' ||
                    strtoupper($headers[$i]) === 'KE SAMSAT' ||
                    strtoupper($headers[$i]) === 'MARGIN') {
                    $sheet->setCellValueExplicit($cell, (float)$val, DataType::TYPE_NUMERIC);
                    continue;
                }

                $sheet->setCellValueExplicit($cell, (string)$val, DataType::TYPE_STRING);

                // Row cancel merah
                if ($isCancel) {
                    $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFF0000');
                }
            }
            $r++;
        }
        $last = $r - 1;

        // Borders & zebra
        $sheet->getStyle("{$cStart}{$hRow}:{$cEnd}{$last}")
              ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        for ($zr = $hRow + 1; $zr <= $last; $zr++) {
            if ($zr % 2 === 0) {
                $sheet->getStyle("{$cStart}{$zr}:{$cEnd}{$zr}")
                      ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF9F9F9');
            }
        }

        if ($last >= $hRow + 1) {
            $dateRange = $sheet->getCellByColumnAndRow($startIdx, $hRow+1)->getCoordinate() . ':' .
                         $sheet->getCellByColumnAndRow($startIdx, $last)->getCoordinate();
            $sheet->getStyle($dateRange)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            $sheet->getStyle($dateRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            foreach (['NOMINAL','TOTAL BAYAR','KE SAMSAT','MARGIN'] as $field) {
                $nomIdx = array_search($field, array_map('strtoupper', $headers), true);
                if ($nomIdx !== false) {
                    $nomColIdx = $startIdx + $nomIdx;
                    $nomRange  = $sheet->getCellByColumnAndRow($nomColIdx, $hRow+1)->getCoordinate() . ':' .
                                 $sheet->getCellByColumnAndRow($nomColIdx, $last)->getCoordinate();
                    $sheet->getStyle($nomRange)->getNumberFormat()->setFormatCode("\"Rp\" #,##0;-"."\"Rp\" #,##0");
                    $sheet->getStyle($nomRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            }
        }

        foreach ($headers as $i => $_) {
            $col = Coordinate::stringFromColumnIndex($startIdx + $i);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle("{$cStart}{$hRow}:{$cEnd}{$last}")->getAlignment()->setWrapText(true);

        return $last;
    }
}
