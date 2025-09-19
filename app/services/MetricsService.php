<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\Sale;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Vehicle;
use App\Models\StnkRenewal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MetricsService
{
    public function summary(Carbon $start = null, Carbon $end = null): array
    {
        $start = $start ?? now()->subDays(7)->startOfDay();
        $end   = $end   ?? now()->subDay()->endOfDay();

        $pengunjung = DB::table('visitors')->whereBetween('created_at', [$start, $end])->count();

        $penjualanJumlah = Sale::whereBetween('sale_date', [$start, $end])->count();
        $penjualanTotal  = Sale::whereBetween('sale_date', [$start, $end])->sum('sale_price');

        $pemasukan     = Income::whereBetween('created_at', [$start, $end])->sum('amount');
        $pengeluaran   = Expense::whereBetween('created_at', [$start, $end])->sum('amount');
        $saldo         = $pemasukan - $pengeluaran;

        $stok = Vehicle::doesntHave('sale')->count();

        $stnk = StnkRenewal::whereBetween('renewal_date', [$start, $end])->count();

        return [
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
