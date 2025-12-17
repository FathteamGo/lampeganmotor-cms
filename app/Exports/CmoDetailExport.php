<?php

namespace App\Exports;

use App\Models\Cmo;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnFormatting,
    WithEvents
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{
    Alignment,
    Border,
    Fill,
    NumberFormat
};
use Maatwebsite\Excel\Events\AfterSheet;

class CmoDetailExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnFormatting,
    WithEvents
{
    protected int $totalTransaksi = 0;
    protected int $totalFee = 0;
    protected Collection $rows;

    public function __construct(
        protected Cmo $cmo,
        protected int $month,
        protected int $year
    ) {}

    public function collection()
    {
        $sales = $this->cmo->sales()
            ->with('customer', 'vehicle.vehicleModel')
            ->whereNotIn('status', ['cancel'])
            ->whereMonth('sale_date', $this->month)
            ->whereYear('sale_date', $this->year)
            ->orderBy('sale_date')
            ->get();

        $this->totalTransaksi = $sales->count();
        $this->totalFee       = $sales->sum('cmo_fee');

        $this->rows = $sales->map(fn ($sale) => [
            $sale->sale_date->format('d M Y'),
            $sale->customer->name,
            optional($sale->vehicle?->vehicleModel)->name ?? '-',
            $sale->vehicle?->license_plate ?? '-',   
            (int) $sale->cmo_fee,
        ]);

        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Customer',
            'Unit / Motor',
            'No. Polisi',
            'Fee CMO',
        ];
    }

    /* ===============================
     * STYLING HEADER
     * =============================== */
    public function styles(Worksheet $sheet)
    {
        $sheet->insertNewRowBefore(1, 4);

        // TITLE
        $sheet->setCellValue('A1', 'LAPORAN DETAIL CMO');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // INFO
        $sheet->setCellValue('A2', 'Nama CMO');
        $sheet->setCellValue('B2', $this->cmo->name);

        $sheet->setCellValue('A3', 'Periode');
        $sheet->setCellValue('B3', "{$this->month} / {$this->year}");

        $sheet->getStyle('A2:A3')->getFont()->setBold(true);

        // TABLE HEADER
        $sheet->getStyle('A5:E5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E7EB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // AUTO SIZE
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    /* ===============================
     * FORMAT ANGKA
     * =============================== */
    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    /* ===============================
     * FOOTER & BORDER
     * =============================== */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $dataStartRow = 6;
                $dataEndRow   = $dataStartRow + $this->rows->count() - 1;

                // BORDER TABLE
                $sheet->getStyle("A5:E{$dataEndRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // SUMMARY
                $summaryRow = $dataEndRow + 2;

                $sheet->setCellValue("A{$summaryRow}", 'Total Transaksi');
                $sheet->setCellValue("B{$summaryRow}", $this->totalTransaksi);

                $sheet->setCellValue("A".($summaryRow + 1), 'Total Fee CMO');
                $sheet->setCellValue("B".($summaryRow + 1), $this->totalFee);

                $sheet->getStyle("A{$summaryRow}:A".($summaryRow + 1))
                    ->getFont()->setBold(true);

                $sheet->getStyle("B".($summaryRow + 1))
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            },
        ];
    }
}
