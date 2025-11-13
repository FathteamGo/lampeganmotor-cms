<?php

namespace App\Exports\Sheets;

use App\Models\StnkRenewal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StnkSheet implements FromCollection, WithHeadings
{
    protected ?string $startDate;
    protected string $endDate;
    protected ?string $search;

    public function __construct(string $startDate, string $endDate, ?string $search = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->search = $search;
    }

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
                'Tanggal'            => $r->tgl,
                'No. Polisi'         => $r->license_plate,
                'Atas Nama STNK'     => $r->atas_nama_stnk,
                'Customer'           => $r->customer->name ?? '-',
                'Nomor Telepon'      => $r->customer->phone ?? '-',
                'Jenis Pekerjaan'    => match ($r->jenis_pekerjaan) {
                    'bbn' => 'BBN Balik Nama',
                    'cetak_ganti' => 'Cetak Ganti STNK/Plat',
                    'perpanjangan' => 'Perpanjangan Tahunan',
                    default => '-',
                },
                'Total Pajak + Jasa' => $r->total_pajak_jasa,
                'Pembayaran ke Samsat' => $r->pembayaran_ke_samsat,
                'Nama Vendor'        => $r->vendor ?? '-',
                'Pembayaran ke Vendor' => $r->payvendor ?? 0,
                'DP / Dibayar'       => $r->dp,
                'Sisa Pembayaran'    => $r->sisa_pembayaran,
                'Margin (Laba)'      => $r->margin_total,
                'Tanggal Diambil'    => $r->diambil_tgl,
                'Status'             => ucfirst($r->status),
            ]);
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'No. Polisi',
            'Atas Nama STNK',
            'Customer',
            'Nomor Telepon',
            'Jenis Pekerjaan',
            'Total Pajak + Jasa',
            'Pembayaran ke Samsat',
            'Nama Vendor',
            'Pembayaran ke Vendor',
            'DP / Dibayar',
            'Sisa Pembayaran',
            'Margin (Laba)',
            'Tanggal Diambil',
            'Status',
        ];
    }
}
