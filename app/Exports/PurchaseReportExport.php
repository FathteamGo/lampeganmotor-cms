<?php

namespace App\Exports;

use App\Models\Purchase;
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
use Illuminate\Support\Carbon;

class PurchaseReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return ($this->query ?? Purchase::query())
            ->with([
                'supplier',
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
            'Supplier',
            'Phone',
            'Address',
            'Brand',
            'Type',
            'Model',
            'Color',
            'Year',
            'VIN',
            'License Plate',
            'Vehicle Status',
            'Total Price',
            'Payment Method',
            'OTR',
            'Additional Fee',
            'DP',
            'Remaining Debt',
            'Branch',
            'Notes',
        ];
    }

    public function map($purchase): array
    {
        return [
            $purchase->id,
            Carbon::parse($purchase->purchase_date)->format('Y-m-d'),
            $purchase->supplier?->name,
            $purchase->supplier?->phone,
            $purchase->supplier?->address,
            $purchase->vehicle?->vehicleModel?->brand?->name,
            $purchase->vehicle?->type?->name,
            $purchase->vehicle?->vehicleModel?->name,
            $purchase->vehicle?->color?->name,
            $purchase->vehicle?->year?->year,
            $purchase->vehicle?->vin,
            $purchase->vehicle?->license_plate,
            $purchase->vehicle?->status,
            $purchase->total_price,
            $purchase->payment_method ?? '',
            $purchase->otr ?? '',
            $purchase->additional_fee ?? '',
            $purchase->dp ?? '',
            $purchase->remaining_debt ?? '',
            $purchase->branch ?? '',
            $purchase->notes ?? '',
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
            'N' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'P' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'Q' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'R' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'S' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
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

                // Border lebih jelas, tapi tetap simpel (medium)
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
