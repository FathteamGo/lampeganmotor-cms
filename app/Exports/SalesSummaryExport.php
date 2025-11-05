<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SalesSummaryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnFormatting
{
    protected $month;
    protected $year;

    public function __construct($month = null, $year = null)
    {
        $this->month = $month ?? now()->format('m');
        $this->year  = $year ?? now()->format('Y');
    }

    public function collection()
    {
        return User::orderBy('name')->get();
    }

    public function map($user): array
    {
        $salesCount = DB::table('sales')
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['cancel'])
            ->whereIn('result', ['ACC', 'CASH'])
            ->whereMonth('sale_date', $this->month)
            ->whereYear('sale_date', $this->year)
            ->count();

        $totalOmzet = DB::table('sales')
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['cancel'])
            ->whereIn('result', ['ACC', 'CASH'])
            ->whereMonth('sale_date', $this->month)
            ->whereYear('sale_date', $this->year)
            ->sum('sale_price');

        $bonus = self::calculateBonus($salesCount);
        $baseSalary = $user->base_salary ?? 0;
        $totalIncome = $baseSalary + $bonus;

        return [
            $user->name,
            $salesCount,
            $totalOmzet,
            $bonus,
            $baseSalary,
            $totalIncome,
        ];
    }

    public function headings(): array
    {
        return [
            // Heading tabel (baris ke-2, karena baris pertama akan diisi periode)
            'Nama Sales',
            'Unit Terjual',
            'Total Omzet (Rp)',
            'Bonus (Rp)',
            'Gaji Pokok (Rp)',
            'Total Penghasilan (Rp)',
        ];
    }

    private static function calculateBonus(int $salesCount): int
    {
        if ($salesCount <= 0) return 0;

        if ($salesCount < 5) return 150_000 * $salesCount;
        if ($salesCount < 10) return 250_000 * $salesCount;
        if ($salesCount == 10) return (250_000 * 10) + 500_000;

        return (250_000 * 10) + 500_000 + (150_000 * ($salesCount - 10));
    }

    public function styles(Worksheet $sheet)
    {
        // Tambah baris di atas untuk keterangan periode
        $monthName = match ($this->month) {
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
            default => $this->month,
        };

        $periodeText = "Periode: {$monthName} {$this->year}";
        $sheet->insertNewRowBefore(1, 1); // Sisipkan baris di atas heading
        $sheet->setCellValue('A1', $periodeText);

        // Gabungkan cell A1 sampai F1
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Style header (baris ke-2 setelah periode)
        $sheet->getStyle('A2:F2')->getFont()->setBold(true);
        $sheet->getStyle('A2:F2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9D9D9');

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }
}
