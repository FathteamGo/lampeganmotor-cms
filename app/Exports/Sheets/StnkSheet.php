<?php

namespace App\Exports\Sheets;

use App\Models\StnkRenewal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StnkSheet implements FromCollection, WithHeadings
{
    public function __construct(
        protected string $startDate,
        protected string $endDate,
        protected ?string $search = null,
    ) {}

    public function collection()
    {
        return StnkRenewal::query()
            ->when($this->startDate, fn ($q) => $q->whereDate('tgl', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('tgl', '<=', $this->endDate))
            ->when($this->search, function ($q, $search) {
                $q->where('license_plate', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($q2) =>
                      $q2->where('name', 'like', "%{$search}%")
                  );
            })
            ->get()
            ->map(fn ($r) => [
                'Tanggal'       => $r->tgl,
                'No. Polisi'    => $r->license_plate,
                'Customer'      => $r->customer->name ?? '-',
                'Total Bayar'   => $r->total_pajak_jasa,
                'Ke Samsat'     => $r->total_pajak_jasa - $r->margin_total,
                'Margin'        => $r->margin_total,
            ]);
    }

    public function headings(): array
    {
        return ['Tanggal', 'No. Polisi', 'Customer', 'Total Bayar', 'Ke Samsat', 'Margin'];
    }
}
