<?php

namespace App\Exports\Sheets;

use App\Models\Sale;
use App\Exports\Sheets\Concerns\SheetStyling;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema as DbSchema;
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
    ) {}

    public function title(): string
    {
        return 'Sales';
    }

    public function array(): array
    {
        // Base kolom wajib (cek eksistensi aman)
        $dateCol   = $this->has('sales', 'sale_date') ? 'sale_date' : (DbSchema::hasColumn('sales','created_at') ? 'created_at' : null);
        $amountCol = $this->has('sales', 'sale_price') ? 'sale_price' : null;

        // Opsional
        $opt = array_filter([
            $this->has('sales', 'payment_method') ? 'payment_method' : null,
            $this->has('sales', 'notes')          ? 'notes' : null,
            $this->has('sales', 'license_plate')  ? 'license_plate' : null,
        ]);

        // Header
        $headers = array_merge(['DATE', 'AMOUNT'], array_map(function ($c) {
            return match ($c) {
                'payment_method' => 'PAYMENT',
                'notes'          => 'NOTE',
                'license_plate'  => 'PLATE',
                default          => strtoupper($c),
            };
        }, $opt));

        $rows = [];
        $query = Sale::query()
            ->when($dateCol, fn ($q) => $q->whereDate($dateCol, '>=', $this->start))
            ->when($dateCol, fn ($q) => $q->whereDate($dateCol, '<=', $this->end))
            ->orderBy($dateCol ?? 'id');

        $selectCols = array_filter([$dateCol, $amountCol, ...$opt]);
        $data = $selectCols ? $query->get($selectCols) : $query->get();

        foreach ($data as $r) {
            $row = [];
            // DATE
            $dateVal = $dateCol ? Carbon::parse($r->{$dateCol}) : null;
            $row[] = $dateVal ? $dateVal->toDateString() : '';
            // AMOUNT
            $row[] = $amountCol ? (float) $r->{$amountCol} : 0;

            // optional
            foreach ($opt as $c) {
                $row[] = (string) ($r->{$c} ?? '');
            }
            $rows[] = $row;
        }

        return [$headers, ...$rows];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                /** @var Worksheet $sheet */
                $sheet = $event->sheet->getDelegate();
                $rowCount = max(1, $sheet->getHighestDataRow());
                $colCount = $sheet->getHighestDataColumn();
                $colCount = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colCount);

                // Terapkan styling tabel
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
            if ($c === $lastCol) break; // PHP's char++ trick
        }
        return $cells;
    }
}
