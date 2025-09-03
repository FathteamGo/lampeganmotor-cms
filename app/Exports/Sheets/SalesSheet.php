<?php

namespace App\Exports\Sheets;

use App\Models\Sale;
use App\Exports\Sheets\Concerns\SheetStyling;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesSheet implements FromArray, WithTitle, WithEvents
{
    use SheetStyling;

    public function __construct(
        protected string $start,
        protected string $end,
        protected ?string $search = null,
    ) {}

    public function title(): string { return 'Sales'; }

    public function array(): array
    {
        $headers = ['TANGGAL','NAMA','KATEGORI','TAHUN','KETERANGAN','NOMINAL','NO INVOICE','TIPE','MODEL','WARNA','METODE'];

        $q = Sale::query()
            ->with(['vehicle.vehicleModel.brand','vehicle.vehicleModel','vehicle.type','vehicle.color','vehicle.year','customer'])
            ->whereBetween('sale_date', [$this->start, $this->end])
            ->orderBy('sale_date');

        // Global search (mirror dari widget SalesTable)
        if (filled($this->search)) {
            $term = trim($this->search);
            $s = "%{$term}%";
            $num = preg_replace('/\D+/', '', $term) ?: null;

            $q->where(function ($qq) use ($s, $num) {
                $qq->where('notes', 'like', $s)
                   ->orWhere('sale_price', 'like', $s)
                   ->when($num, fn ($w) => $w->orWhere('id', (int) $num))
                   ->orWhereHas('customer', fn ($x) => $x->where('name', 'like', $s))
                   ->orWhereHas('vehicle.vehicleModel', fn ($x) => $x->where('name', 'like', $s))
                   ->orWhereHas('vehicle.vehicleModel.brand', fn ($x) => $x->where('name', 'like', $s));
            });
        }

        $rows = [];
        foreach ($q->get() as $r) {
            $rows[] = [
                Carbon::parse($r->sale_date)->toDateString(),
                (string) optional($r->customer)->name,
                (string) optional(optional(optional($r->vehicle)->vehicleModel)->brand)->name,
                (string) optional(optional($r->vehicle)->year)->year,
                (string) ($r->notes ?? ''),
                (float) ($r->sale_price ?? 0),
                'INV' . str_pad((string) $r->id, 7, '0', STR_PAD_LEFT),
                (string) optional(optional($r->vehicle)->type)->name,
                (string) optional(optional($r->vehicle)->vehicleModel)->name,
                (string) optional(optional($r->vehicle)->color)->name,
                (string) ($r->payment_method ?? ''),
            ];
        }

        return [$headers, ...$rows];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowCount = max(1, $sheet->getHighestDataRow());
                $colCount = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
                $this->applyTableStyles($sheet, $colCount, $rowCount, $this->headerValues($sheet));
            }
        ];
    }

    protected function headerValues(Worksheet $sheet): array
    {
        $lastCol = $sheet->getHighestDataColumn();
        $cells = [];
        for ($c = 'A'; $c <= $lastCol; $c++) {
            $cells[] = (string) $sheet->getCell($c.'1')->getValue();
            if ($c === $lastCol) break;
        }
        return $cells;
    }
}
