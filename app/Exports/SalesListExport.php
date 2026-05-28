<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class SalesListExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting, WithEvents
{
    protected Collection $records;
    protected int $index = 0;
    
    protected float $totalPembelian = 0;
    protected float $totalOtr = 0;
    protected float $totalLabaKotor = 0;
    protected float $totalLabaBersih = 0;

    public function __construct(Collection $records)
    {
        $this->records = $records;
    }

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Jenis Motor',
            'H-Total Pembelian',
            'OTR',
            'Laba Kotor',
            'Laba Bersih',
            'Metode Pembayaran',
            'Status',
            'Tanggal',
        ];
    }

    public function map($row): array
    {
        $this->index++;

        $purchase = $row->purchase;
        $hargaTotalPembelian = $purchase ? (float) $purchase->grand_total : 0;
        if ($hargaTotalPembelian == 0) {
            $hargaTotalPembelian = (float) optional($row->vehicle)->purchase_price;
        }

        $otr = (float) ($row->sale_price ?? 0);
        
        $isCancel = $row->status === 'cancel';
        $labaKotor = $isCancel ? 0 : (float) $row->laba_kotor;
        $labaBersih = $isCancel ? 0 : (float) $row->laba_bersih;

        // Tambahkan ke variabel total
        $this->totalPembelian += $hargaTotalPembelian;
        $this->totalOtr += $otr;
        $this->totalLabaKotor += $labaKotor;
        $this->totalLabaBersih += $labaBersih;

        $paymentMethod = match($row->payment_method) {
            'cash' => 'Cash',
            'credit' => 'Credit',
            'tukartambah' => 'Tukar Tambah',
            'cash_tempo' => 'Cash Tempo',
            default => $row->payment_method
        };

        return [
            $this->index,
            $row->customer->name ?? '-',
            $row->vehicle?->vehicleModel?->name ?? '-',
            $hargaTotalPembelian,
            $otr,
            $labaKotor,
            $labaBersih,
            $paymentMethod,
            strtoupper($row->status),
            $row->sale_date ? $row->sale_date->format('Y-m-d') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA'], // Light green header
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0',
            'E' => '#,##0',
            'F' => '#,##0',
            'G' => '#,##0',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $this->index + 1; // 1 header row + N data rows
                $sumRow = $lastRow + 1;

                // Tambahkan teks TOTAL
                $sheet->setCellValue('A' . $sumRow, 'TOTAL');
                $sheet->mergeCells('A' . $sumRow . ':C' . $sumRow);
                $sheet->getStyle('A' . $sumRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Tambahkan nilai SUM
                $sheet->setCellValue('D' . $sumRow, $this->totalPembelian);
                $sheet->setCellValue('E' . $sumRow, $this->totalOtr);
                $sheet->setCellValue('F' . $sumRow, $this->totalLabaKotor);
                $sheet->setCellValue('G' . $sumRow, $this->totalLabaBersih);

                // Styling row SUM
                $sheet->getStyle('A' . $sumRow . ':J' . $sumRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFF00'], // Yellow background for totals
                    ],
                ]);

                // Auto size semua kolom
                foreach (range('A', 'J') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
                // Tambahkan border untuk semua data
                $sheet->getStyle('A1:J' . $sumRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
}
