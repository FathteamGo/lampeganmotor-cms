<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Sale;
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

class SalesDetailPerSalesExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnFormatting,
    WithEvents
{
    protected int $totalUnit = 0;
    protected int $totalOmzet = 0;
    protected int $feePerUnitTotal = 0;
    protected int $bonus = 0;
    protected Collection $rows;

    public function __construct(
        protected User $user,
        protected int $month,
        protected int $year
    ) {}

    public function collection()
    {
        $sales = Sale::query()
            ->with(['customer', 'vehicle.vehicleModel', 'vehicle.year'])
            ->where('user_id', $this->user->id)
            ->whereNotIn('status', ['cancel'])
            ->whereMonth('sale_date', $this->month)
            ->whereYear('sale_date', $this->year)
            ->orderBy('sale_date')
            ->get();

        $this->totalUnit  = $sales->count();
        $this->totalOmzet = $sales->sum('sale_price');
        $this->bonus      = $this->calculateBonus($this->totalUnit);
        
        // Fee Per Unit logic:
        // Jika < 10 unit = 150rb/unit, 
        // Lanjutan rumus dari table (tetapi untuk list per item, kita perlu tahu nilai per itemnya berapa).
        // Sesuai rumus calculateBonus:
        // Unit 1-4: @150 (Total 600)
        // Unit 5-9: @250 (Total 1.250 + 600 ? Tidak, logic di calculateBonus itu FLAT per tier total, bukan progresif per item).
        // Mari lihat logic calculateBonus yang ada:
        // < 5 : 150k * count
        // < 10 : 250k * count
        // == 10 : (250k*10) + 500k
        // > 10 : (250k*10) + 500k + (150k * (count-10))
        
        // Untuk tampilan "FEE PER UNIT" di tabel, biasanya user ingin melihat nilai rata-rata atau nilai atribusi per unit.
        // TAPI dikarenakan gambarnya menunjukkan kolom "FEE PER UNIT", kita akan hitung manual per baris agar totalnya match atau pakai nilai estimasi.
        // Di gambar terlihat angka "150", "100", dll. (dalam ribuan).
        // Karena logic bonus agak kompleks (bukan flat fee per unit sebenarnya), kita akan coba simulasikan
        // atau kita kosongkan/defaultkan dulu jika tidak ada field khusus 'fee' di tabel sales untuk sales person.
        // ASUMSI: Dari gambar, sepertinya user ingin inputan atau nilai statis.
        // Namun karena sistem menghitung bonus secara global, kita akan membagi bonus proporsional atau menampilkan '-' jika belum ada logic per unit.
        // TAPI dari gambar terlihat angka 150, 100, 100, 100...
        // Mari kita defaultkan ke logic < 5 -> 150.000, 5-9 -> 250.000 sementara, atau kita ambil dari logic bonus dibagi rata?
        // UPDATE: Sesuai gambar, kolom G ada isinya. Kita akan pasang logic estimasi sederhana:
        // Jika total < 5, per unit 150.000. Jika >= 5, per unit 250.000.
        // Pengecualian > 10 unit, 10 pertama 250.000, sisanya 150.000.
        
        $this->rows = $sales->map(function ($sale, $index) {
            // Logic Fee Per Unit based on urutan (index 0-based)
            $fee = 0;
            $salesCount = $this->totalUnit;
            
            // Urutan ke-n (1-based)
            $n = $index + 1;
            
            if ($salesCount < 5) {
                $fee = 150000;
            } elseif ($salesCount < 10) {
                $fee = 250000;
            } else {
                // Skema > 10
                // 10 unit pertama dapat jatah dari base bonus (3.000.000 / 10 = 300.000? Atau 250rb + prorata 500rb?)
                // Mari sederhanakan sesuai visual gambar: User minta struktur Excelnya dulu yg bener.
                // Value fee per unit kita set sesuai logic sales sementara:
                if ($n <= 10) {
                    $fee = 250000; 
                    // Note: Bonus tambahan 500rb di unit ke-10 biasanya lumpsum, tidak dipecah per unit di list ini biasanya.
                } else {
                    $fee = 150000;
                }
            }
            
            $this->feePerUnitTotal += $fee;

            return [
                $n, // No
                $sale->customer->name ?? '-', // Nama Customer
                $sale->customer->phone ?? '-', // No TLP
                optional($sale->vehicle?->vehicleModel)->name ?? '-', // Unit/Motor
                optional($sale->vehicle?->year)->year ?? '-', // Tahun
                $sale->vehicle?->license_plate ?? '-', // Nopol
                $fee, // FEE PER UNIT
                $sale->sale_date->format('d/m/Y'), // Tanggal
                $sale->order_source ?? '-', // Sumber Order
                $sale->payment_method ?? '-', // Metode Pembayaran
            ];
        });

        // RE-CALCULATE BONUS AGAR MATCH TOTAL
        // Total bonus akhir tetap pakai rumus global calculateBonus() agar akurat secara total gaji.
        // Fee di atas hanya tampilan per baris.

        return $this->rows;
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

    public function styles(Worksheet $sheet)
    {
        // 1. Insert Header Rows
        $sheet->insertNewRowBefore(1, 4);

        // 2. Main Title
        $sheet->setCellValue('E1', 'Laporan Penjualan Sales');
        $sheet->getStyle('E1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        
        // 3. Info Sales
        $sheet->setCellValue('C2', 'Nama Sales');
        $sheet->setCellValue('C3', 'Periode');
        
        $sheet->setCellValue('F2', strtoupper($this->user->name));
        $sheet->setCellValue('F3', "{$this->month} / {$this->year}");
        
        // 4. Table Header Styling (Yellow, Bold, Border)
        $sheet->getStyle('A5:J5')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00'], // Yellow
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
        
        // Note di bawah header Fee
        $sheet->setCellValue('G6', 'BUKAN HARGA TERJUAL');
        $sheet->getStyle('G6')->applyFromArray([
            'font' => ['bold' => true, 'italic' => true, 'size' => 8],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);
        
        return [];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0', // Fee Format
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowCount = $this->rows->count();
                $lastDataRow = 5 + $rowCount; // Header di baris 5

                // 1. Border untuk area Data
                if ($rowCount > 0) {
                    $sheet->getStyle("A5:J{$lastDataRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }

                // 2. Summary Section Layout (Dimulai dari Kolom D / Unit)
                $summaryStartRow = $lastDataRow + 2;
                
                // Style untuk summary area (Blue background)
                $summaryStyle = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'B4C6E7'], // Light Blue
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'font' => ['bold' => true],
                ];

                // Labels
                $sheet->setCellValue("D{$summaryStartRow}", 'FEE PER UNIT');
                $sheet->setCellValue("D".($summaryStartRow+1), 'Total Unit Terjual');
                
                // Total Omzet (Red text)
                $sheet->setCellValue("D".($summaryStartRow+2), 'Total Omzet');
                $sheet->getStyle("D".($summaryStartRow+2))->getFont()->getColor()->setRGB('FF0000'); // Red
                $sheet->getStyle("G".($summaryStartRow+2))->getFont()->getColor()->setRGB('FF0000'); // Red

                $sheet->setCellValue("D".($summaryStartRow+3), 'Bonus');
                $sheet->setCellValue("D".($summaryStartRow+4), 'Gaji Pokok');
                $sheet->setCellValue("D".($summaryStartRow+5), 'Lembur');
                $sheet->setCellValue("D".($summaryStartRow+6), 'Total Penghasilan');

                // Values (Di kolom G supaya lurus dengan Fee Per Unit)
                $sheet->setCellValue("G{$summaryStartRow}", $this->feePerUnitTotal);
                $sheet->setCellValue("G".($summaryStartRow+1), $this->totalUnit);
                $sheet->setCellValue("G".($summaryStartRow+2), $this->totalOmzet);
                $sheet->setCellValue("G".($summaryStartRow+3), $this->bonus);
                $sheet->setCellValue("G".($summaryStartRow+4), $this->user->base_salary ?? 0);
                $sheet->setCellValue("G".($summaryStartRow+5), $this->user->overtime ?? 0);
                
                $totalPenghasilan = ($this->user->base_salary ?? 0) + ($this->user->overtime ?? 0) + $this->bonus;
                $sheet->setCellValue("G".($summaryStartRow+6), $totalPenghasilan);

                // Merging label cells (D sampai F)
                for ($i = 0; $i <= 6; $i++) {
                    $row = $summaryStartRow + $i;
                    $sheet->mergeCells("D{$row}:F{$row}");
                }

                // Apply Styles to Summary Area
                $range = "D{$summaryStartRow}:G".($summaryStartRow + 6);
                $sheet->getStyle($range)->applyFromArray($summaryStyle);

                // Format Angka Summary
                $sheet->getStyle("G{$summaryStartRow}:G".($summaryStartRow + 6))
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Auto Size Columns
                foreach (range('A', 'J') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }

    private function calculateBonus(int $salesCount): int
    {
        if ($salesCount <= 0) return 0;
        if ($salesCount < 5) return 150_000 * $salesCount;
        if ($salesCount < 10) return 250_000 * $salesCount;
        if ($salesCount === 10) return (250_000 * 10) + 500_000;
        return (250_000 * 10) + 500_000 + (150_000 * ($salesCount - 10));
    }
}
