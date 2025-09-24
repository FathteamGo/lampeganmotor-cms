<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\Sale;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Vehicle;
use App\Models\StnkRenewal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MetricsService
{
    public function summary(Carbon $start = null, Carbon $end = null): array
    {
        $start = $start ?? now()->startOfWeek(Carbon::MONDAY);
        $end   = $end   ?? now()->endOfDay();

        $pengunjung = DB::table('visitors')->whereBetween('created_at', [$start, $end])->count();
        $penjualanJumlah = Sale::whereBetween('sale_date', [$start, $end])->count();
        $penjualanTotal  = Sale::whereBetween('sale_date', [$start, $end])->sum('sale_price');

        $pemasukan   = Income::whereBetween('created_at', [$start, $end])->sum('amount');
        $pengeluaran = Expense::whereBetween('created_at', [$start, $end])->sum('amount');
        $saldo       = $pemasukan - $pengeluaran;

        $stok = Vehicle::doesntHave('sale')->count();
        $stnk = StnkRenewal::whereBetween('renewal_date', [$start, $end])->count();

        return [
            'periode' => [
                'mulai'   => $start->toDateString(),
                'selesai' => $end->toDateString(),
            ],
            'pengunjung' => $pengunjung,
            'penjualan' => [
                'jumlah' => $penjualanJumlah,
                'total'  => $penjualanTotal,
            ],
            'keuangan' => [
                'pemasukan'   => $pemasukan,
                'pengeluaran' => $pengeluaran,
                'saldo'       => $saldo,
            ],
            'stok' => $stok,
            'perpanjangan_stnk' => $stnk,
        ];
    }
}
