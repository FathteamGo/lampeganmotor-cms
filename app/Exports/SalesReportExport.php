<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return ($this->query ?? Sale::query())
            ->with([
                'customer',
                'vehicle.vehicleModel.brand',
                'vehicle.type',
                'vehicle.color',
                'vehicle.year',
            ])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Date',
            'Customer',
            'Phone',
            'Location',
            'Brand',
            'Type',
            'Model',
            'Color',
            'Year',
            'VIN',
            'License Plate',
            'Sale Price',
            'Payment Method',
            'Total Price',
            'OTR',
            'DP PO',
            'DP Real',
            'Piutang',
            'Total Penjualan',
            'Net Profit',
            'Ket',
            'CMO',
            'Fee CMO',
            'Order Source',
            'Ex',
            'Branch',
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->id,
            Carbon::parse($sale->sale_date)->format('Y-m-d'),
            $sale->customer?->name,
            $sale->customer?->phone,
            $sale->customer?->address,
            $sale->vehicle?->vehicleModel?->brand?->name,
            $sale->vehicle?->type?->name,
            $sale->vehicle?->vehicleModel?->name,
            $sale->vehicle?->color?->name,
            $sale->vehicle?->year?->year,
            $sale->vehicle?->vin,
            $sale->vehicle?->license_plate,
            $sale->sale_price,
            $sale->payment_method,
            '', '', '', '', '', '', '', '', '', '', '', '', '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // header bold
        ];
    }

    public function columnFormats(): array
    {
        return [
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Sale Price
            'N' => NumberFormat::FORMAT_TEXT, // Payment Method
            'O' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Total Price
            'P' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // OTR
            'Q' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // DP PO
            'R' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // DP Real
            'S' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Piutang
            'T' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Total Penjualan
            'U' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Net Profit
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $range = "A1:{$highestColumn}{$highestRow}";

                // Border rapih
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // Header align center
                $sheet->getStyle("A1:{$highestColumn}1")->getAlignment()->setHorizontal('center');
            },
        ];
    }
}
