<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CmoIncomeExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $cmoId;
    protected $user;
    protected $salesData;
    protected int $totalUnit = 0;
    protected int $totalOmzet = 0;
    protected int $bonus = 0;

    public function __construct($cmoId)
    {
        $this->cmoId = $cmoId;
        $this->user = User::find($cmoId);
    }

    public function collection()
    {
        $sales = Sale::with(['customer', 'vehicle.vehicleModel', 'vehicle.year'])
            ->where('user_id', $this->cmoId)
            ->whereNotIn('status', ['cancel'])
            ->whereIn('result', ['ACC', 'CASH'])
            ->orderBy('sale_date')
            ->get();

        $this->totalUnit  = $sales->count();
        $this->totalOmzet = $sales->sum('sale_price');
        $this->bonus      = $this->calculateBonus($this->totalUnit);

        $this->salesData = $sales->map(function ($sale, $index) {
            return [
                'no' => $index + 1,
                'customer_name' => $sale->customer?->name ?? '-',
                'customer_phone' => $sale->customer?->phone ?? '-',
                'unit_motor' => $sale->vehicle?->vehicleModel?->name ?? '-',
                'tahun' => $sale->vehicle?->year?->name ?? '-',
                'nopol' => $sale->vehicle?->license_plate ?? '-',
                'fee_per_unit' => $sale->cmo_fee ?? 0,
                'tanggal' => $sale->sale_date?->format('d/m/Y') ?? '-',
                'sumber_order' => $this->formatOrderSource($sale->order_source),
                'metode_pembayaran' => $this->formatPaymentMethod($sale->payment_method),
            ];
        });

        return $this->salesData;
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA CUSTOMER',
            'NO TLP',
            'Unit / Motor',
            'TAHUN',
            'NOPOL',
            'FEE PER UNIT',
            'TANGGAL',
            'SUMBER ORDER',
            'METODE PEMBAYARAN',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 25,
            'C' => 15,
            'D' => 25,
            'E' => 10,
            'F' => 12,
            'G' => 18,
            'H' => 12,
            'I' => 15,
            'J' => 22,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $dataCount = $this->salesData->count();
                $lastDataRow = $dataCount + 5;

                // ===== INSERT HEADER ROWS =====
                $sheet->insertNewRowBefore(1, 4);

                // Title
                $sheet->setCellValue('A1', 'Laporan Penjualan Sales');
                $sheet->mergeCells('A1:J1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Nama Sales
                $sheet->setCellValue('D2', 'Nama Sales');
                $sheet->setCellValue('G2', $this->user?->name ?? '-');

                // Periode
                $sheet->setCellValue('D3', 'Periode');
                $sheet->setCellValue('G3', 'Semua Data');

                // ===== TABLE HEADER STYLING (Row 5) =====
                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '808000'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ];
                $sheet->getStyle('A5:J5')->applyFromArray($headerStyle);

                // ===== DATA ROWS STYLING =====
                if ($dataCount > 0) {
                    $dataRange = 'A6:J' . $lastDataRow;
                    $sheet->getStyle($dataRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    $sheet->getStyle('G6:G' . $lastDataRow)->getNumberFormat()
                        ->setFormatCode('"Rp" #,##0');
                }

                // ===== FOOTER (SUMMARY) =====
                $footerStartRow = $lastDataRow + 2;

                $summaryData = [
                    ['Total Unit Terjual', $this->totalUnit],
                    ['Total Omzet', $this->totalOmzet],
                    ['Bonus', $this->bonus],
                    ['Gaji Pokok', $this->user?->base_salary ?? 0],
                    ['Lembur', $this->user?->overtime ?? 0],
                    ['Total Penghasilan', ($this->user?->base_salary ?? 0) + ($this->user?->overtime ?? 0) + $this->bonus],
                ];

                foreach ($summaryData as $index => $data) {
                    $row = $footerStartRow + $index;
                    $sheet->setCellValue("D{$row}", $data[0]);
                    $sheet->setCellValue("G{$row}", 'Rp');
                    $sheet->setCellValue("H{$row}", $data[1]);

                    $sheet->getStyle("D{$row}:H{$row}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    $sheet->getStyle("H{$row}")->getNumberFormat()
                        ->setFormatCode('#,##0');
                }

                // Highlight Bonus row
                $bonusRow = $footerStartRow + 2;
                $sheet->getStyle("D{$bonusRow}:H{$bonusRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFFF00');

                $sheet->getStyle('H' . $footerStartRow . ':H' . ($footerStartRow + 5))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }

    private function formatOrderSource(?string $source): string
    {
        return match ($source) {
            'fb' => 'Facebook',
            'ig' => 'Instagram',
            'tiktok' => 'TikTok',
            'walk_in' => 'Walk In',
            default => '-',
        };
    }

    private function formatPaymentMethod(?string $method): string
    {
        return match ($method) {
            'cash' => 'CASH',
            'credit' => 'KREDIT',
            'cash_tempo' => 'CASH TEMPO',
            'transfer' => 'TRANSFER',
            default => '-',
        };
    }

    private function calculateBonus(int $salesCount): int
    {
        if ($salesCount <= 0) return 0;
        if ($salesCount < 5) return 150_000 * $salesCount;
        if ($salesCount < 10) return 250_000 * $salesCount;
        if ($salesCount == 10) return (250_000 * 10) + 500_000;
        return (250_000 * 10) + 500_000 + (150_000 * ($salesCount - 10));
    }
}
