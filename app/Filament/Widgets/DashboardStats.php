<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Ambil data dari database
        $stokUnit = Vehicle::where('status', 'available')->count();
        $terjualBulanIni = Sale::whereYear('sale_date', Carbon::now()->year)
            ->whereMonth('sale_date', Carbon::now()->month)
            ->count();
        $terjualHariIni = Sale::whereDate('sale_date', Carbon::today())->count();

        $totalPenjualanTahunIni = Sale::whereYear('sale_date', Carbon::now()->year)->sum('sale_price');

        return [
            Stat::make('Stok', $stokUnit)
                ->description('Total unit tersedia')
                ->color('primary'),

            Stat::make('Terjual Bulan Ini', $terjualBulanIni . ' Unit')
                ->description('Jumlah unit terjual bulan ini')
                ->color('success'),

            Stat::make('Terjual Hari Ini', $terjualHariIni . ' Unit')
                ->description('Jumlah unit terjual hari ini')
                ->color('success'),

            Stat::make('Total Penjualan Tahun Ini', 'Rp ' . number_format($totalPenjualanTahunIni, 0, ',', '.'))
                ->description('Akumulasi penjualan tahun ini')
                ->color('warning'),

            // Anda bisa tambahkan Stat::make() lainnya untuk kartu Total Pengeluaran, Total Aset, dll.
            // dengan logika query yang serupa.
        ];
    }
}
