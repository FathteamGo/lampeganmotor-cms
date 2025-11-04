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
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        return User::all();
    }

    public function map($user): array
    {
        $unit = DB::table('sales')
            ->where('user_id', $user->id)
            ->where('status','!=','cancel')
            ->when($this->month && $this->year, fn($q) => $q->whereMonth('sale_date', $this->month)
                                                           ->whereYear('sale_date', $this->year))
            ->count();

        $totalOmzet = DB::table('sales')
            ->where('user_id', $user->id)
            ->where('status','!=','cancel')
            ->when($this->month && $this->year, fn($q) => $q->whereMonth('sale_date', $this->month)
                                                           ->whereYear('sale_date', $this->year))
            ->sum('sale_price');

        $bonus = $user->bonus ?? 0;
        $salary = $user->base_salary ?? 0;

        return [
            $user->name,
            $unit,
            $totalOmzet,
            $bonus,
            $salary,
            $bonus + $salary,
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Sales',
            'Unit Terjual',
            'Total Omzet (Rp)',
            'Bonus (Rp)',
            'Gaji Pokok (Rp)',
            'Total Penghasilan (Rp)',
        ];
    }

    // Styling header dan sheet
    public function styles(Worksheet $sheet)
    {
        // Header bold & background abu-abu
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9D9D9');

        // Auto-fit kolom
        foreach(range('A','F') as $col){
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    // Format kolom mata uang
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
