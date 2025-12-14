<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SalesDetailPerSalesExport implements FromCollection, WithHeadings, WithStyles, WithColumnFormatting
{
    protected User $user;
    protected int $month;
    protected int $year;

    protected int $totalUnit = 0;
    protected int $totalOmzet = 0;
    protected int $bonus = 0;

    public function __construct(User $user, int $month, int $year)
    {
        $this->user  = $user;
        $this->month = $month;
        $this->year  = $year;
    }

    public function collection()
    {
        $sales = DB::table('sales')
            ->leftJoin('vehicles', 'vehicles.id', '=', 'sales.vehicle_id')
            ->leftJoin('vehicle_models', 'vehicle_models.id', '=', 'vehicles.vehicle_model_id')
            ->select([
                'vehicle_models.name as unit',
                'sales.sale_price',
            ])
            ->where('sales.user_id', $this->user->id)
            ->whereNotIn('sales.status', ['cancel'])
            ->whereMonth('sales.sale_date', $this->month)
            ->whereYear('sales.sale_date', $this->year)
            ->orderBy('sales.sale_date')
            ->get();

        $this->totalUnit  = $sales->count();
        $this->totalOmzet = $sales->sum('sale_price');
        $this->bonus      = $this->calculateBonus($this->totalUnit);

        return $sales;
    }

    public function headings(): array
    {
        return [
            'Unit / Motor',
            'Harga Terjual (Rp)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // ===== HEADER =====
        $sheet->insertNewRowBefore(1, 4);

        $sheet->setCellValue('A1', 'Laporan Penjualan Sales');
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A2', 'Nama Sales');
        $sheet->setCellValue('B2', $this->user->name);

        $sheet->setCellValue('A3', 'Periode');
        $sheet->setCellValue('B3', "{$this->month} / {$this->year}");

        // ===== TABLE HEADER =====
        $sheet->getStyle('A5:B5')->getFont()->setBold(true);

        // ===== FOOTER (RINGKASAN) =====
        $lastRow = $sheet->getHighestRow() + 2;

        $sheet->setCellValue("A{$lastRow}", 'Total Unit Terjual');
        $sheet->setCellValue("B{$lastRow}", $this->totalUnit);

        $sheet->setCellValue("A".($lastRow+1), 'Total Omzet');
        $sheet->setCellValue("B".($lastRow+1), $this->totalOmzet);

        $sheet->setCellValue("A".($lastRow+2), 'Bonus');
        $sheet->setCellValue("B".($lastRow+2), $this->bonus);

        $sheet->setCellValue("A".($lastRow+3), 'Gaji Pokok');
        $sheet->setCellValue("B".($lastRow+3), $this->user->base_salary ?? 0);

        $sheet->setCellValue("A".($lastRow+4), 'Lembur');
        $sheet->setCellValue("B".($lastRow+4), $this->user->overtime ?? 0);

        $sheet->setCellValue("A".($lastRow+5), 'Total Penghasilan');
        $sheet->setCellValue(
            "B".($lastRow+5),
            ($this->user->base_salary ?? 0)
            + ($this->user->overtime ?? 0)
            + $this->bonus
        );

        foreach (range('A', 'B') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
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
