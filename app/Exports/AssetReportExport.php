<?php
namespace App\Exports;

use App\Models\OtherAsset;
use App\Models\Sale;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AssetReportExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Harta Tidak Bergerak
        $otherAssets = OtherAsset::all()->map(function ($item) {
            return [
                'Jenis'      => 'Harta Tidak Bergerak',
                'Nama'       => $item->name,
                'Kategori'   => $item->category ?? '-',
                'Tahun'      => $item->year ?? '-',
                'Keterangan' => $item->description,
                'Nominal'    => $item->value,
            ];
        });

        // Stok Unit Bergerak
        $vehicles = Vehicle::with(['vehicleModel', 'year'])
            ->where('status', 'available')
            ->get()
            ->map(function ($item) {
                return [
                    'Jenis'      => 'Stok Unit Bergerak',
                    'Nama'       => $item->vehicleModel->name ?? '-',
                    'Kategori'   => 'Kendaraan',
                    'Tahun'      => $item->year->year ?? '-',
                    'Keterangan' => $item->license_plate,
                    'Nominal'    => $item->purchase_price,
                ];
            });

        // Tunggakan
        $tunggakan = Sale::with(['vehicle.vehicleModel', 'vehicle.year'])
            ->whereNotIn('payment_method', ['cash', 'credit']) // ✅ perbaikan disini
            ->get()
            ->map(function ($item) {
                return [
                    'Jenis'      => 'Tunggakan',
                    'Nama'       => $item->vehicle->vehicleModel->name ?? '-',
                    'Kategori'   => 'Kredit',
                    'Tahun'      => $item->vehicle->year->year ?? '-',
                    'Keterangan' => $item->notes ?? '-',
                    'Nominal'    => $item->amount,
                ];
            });

        // Summary total
        $summary = collect([
            [
                'Jenis'      => 'RINGKASAN',
                'Nama'       => 'Total Asset',
                'Kategori'   => '-',
                'Tahun'      => '-',
                'Keterangan' => '-',
                'Nominal'    =>
                $otherAssets->sum('Nominal') +
                $vehicles->sum('Nominal') +
                $tunggakan->sum('Nominal'),
            ],
        ]);

        // Gabung semua
        return $otherAssets
            ->concat($vehicles)
            ->concat($tunggakan)
            ->concat($summary);
    }

    public function headings(): array
    {
        return ['Jenis', 'Nama', 'Kategori', 'Tahun', 'Keterangan', 'Nominal'];
    }
}
