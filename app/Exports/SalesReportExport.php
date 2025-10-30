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
    private int $rowNumber = 0;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    public function collection()
    {
        // Filter biar transaksi cancel gak ikut
        return ($this->query ?? Sale::query())
            ->where('status', '!=', 'cancel')
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

        // 🔹 Kalau status cancel, set semua nilai transaksi ke 0
        if ($sale->status === 'cancel') {
            $salePrice = $dpPo = $dpReal = $cmoFee = $directCommission = 0;
        }

        // 🔹 Hitung Pencairan sesuai metode pembayaran
        $pencairan = match ($sale->payment_method) {
            'cash', 'tukartambah' => $salePrice,
            'credit', 'cash_tempo' => $dpReal + ($sale->remaining_payment ?? 0),
            default => $salePrice
        };

        // 🔹 Hitung Laba Bersih
        $labaBersih = $pencairan - $purchasePrice - $cmoFee - $directCommission;

        return [
            $this->rowNumber,
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
            $salePrice,
            $labaBersih,
            match ($sale->payment_method) {
                'cash' => 'Cash',
                'credit' => 'Credit',
                'tukartambah' => 'Tukar Tambah',
                'cash_tempo' => 'Cash Tempo',
                default => '-'
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
                'cancel' => 'Cancel',
                default => '-'
            },
            $sale->notes ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
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
            'U' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Sisa Pembayaran
            'X' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Fee CMO
            'Y' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Komisi Langsung
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

                // border semua cell
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // center heading
                $sheet->getStyle("A1:{$highestColumn}1")->getAlignment()->setHorizontal('center');
            },
        ];
    }
}
