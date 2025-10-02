<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithColumnFormatting,
    WithEvents
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected $query;
    private int $rowNumber = 0; // nomor urut

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
                'user'
            ])
            ->get();
    }

    public function headings(): array
    {
        return [
            'No', 'Tanggal', 'Pelanggan', 'Telepon', 'Lokasi',
            'Merk', 'Tipe', 'Model', 'Warna', 'Tahun',
            'VIN', 'Plat Nomor', 'H TOTAL PEMBELIAN', 'OTR',
            'DP PO', 'DP REAL', 'PENCAIRAN', 'TOTAL PENJUALAN', 'LABA BERSIH',
            'Metode Pembayaran', 'Sisa Pembayaran', 'Jatuh Tempo',
            'CMO / Mediator', 'Fee CMO', 'Komisi Langsung',
            'Ex', 'Cabang', 'Hasil', 'Sumber Order', 'Status', 'Catatan'
        ];
    }

    public function map($sale): array
    {
        $this->rowNumber++;

        $purchasePrice = $sale->vehicle?->purchase_price ?? 0;
        $salePrice     = $sale->sale_price ?? 0;
        $dpPo          = $sale->dp_po ?? 0;
        $dpReal        = $sale->dp_real ?? 0;
        $cmoFee        = $sale->cmo_fee ?? 0;
        $directCommission = $sale->direct_commission ?? 0;

        // 🔹 Hitung Pencairan sesuai metode pembayaran
        $pencairan = match ($sale->payment_method) {
            'cash', 'tukartambah' => $salePrice,
            'credit', 'cash_tempo' => $dpReal + ($sale->remaining_payment ?? 0),
            default => $salePrice
        };

        // 🔹 Hitung Laba Bersih
        $labaBersih = $pencairan - $purchasePrice - $cmoFee - $directCommission;

        return [
            $this->rowNumber, // nomor urut otomatis
            $sale->sale_date ? Carbon::parse($sale->sale_date)->format('d F Y') : '-',
            $sale->customer?->name ?? '-',
            $sale->customer?->phone ?? '-',
            $sale->customer?->address ?? '-',
            $sale->vehicle?->vehicleModel?->brand?->name ?? '-',
            $sale->vehicle?->type?->name ?? '-',
            $sale->vehicle?->vehicleModel?->name ?? '-',
            $sale->vehicle?->color?->name ?? '-',
            $sale->vehicle?->year?->year ?? '-',
            $sale->vehicle?->vin ?? '-',
            $sale->vehicle?->license_plate ?? '-',
            $purchasePrice,
            $salePrice,
            $dpPo,
            $dpReal,
            $pencairan,
            $salePrice,   // 👉 Total Penjualan = Harga Jual
            $labaBersih,  // 👉 sesuai rumus
            match ($sale->payment_method) {
                'cash' => 'Cash',
                'credit' => 'Credit',
                'tukartambah' => 'Tukar Tambah',
                'cash_tempo' => 'Cash Tempo',
                default => $sale->payment_method ?? '-'
            },
            $sale->remaining_payment ?? 0,
            $sale->due_date ? Carbon::parse($sale->due_date)->format('d F Y') : '-',
            $sale->cmo ?? '-',
            $cmoFee,
            $directCommission,
            $sale->user?->name ?? '-',
            $sale->branch_name ?? '-',
            $sale->result ?? '-',
            match ($sale->order_source) {
                'fb' => 'Facebook',
                'ig' => 'Instagram',
                'tiktok' => 'TikTok',
                'walk_in' => 'Walk In',
                default => '-'
            },
            match ($sale->status) {
                'proses' => 'Proses',
                'kirim' => 'Kirim',
                'selesai' => 'Selesai',
                default => '-'
            },
            $sale->notes ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // judul bold
        ];
    }

    public function columnFormats(): array
    {
        return [
            'M' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // H TOTAL PEMBELIAN
            'N' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // OTR
            'O' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // DP PO
            'P' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // DP REAL
            'Q' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Pencairan
            'R' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Total Penjualan
            'S' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Laba Bersih
            'Y' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Fee CMO
            'Z' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Komisi Langsung
            'T' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Sisa Pembayaran (posisi kolom T tergantung headings)
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

                // border all cells
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000']
                        ]
                    ]
                ]);

                // center heading
                $sheet->getStyle("A1:{$highestColumn}1")->getAlignment()->setHorizontal('center');
            },
        ];
    }
}
