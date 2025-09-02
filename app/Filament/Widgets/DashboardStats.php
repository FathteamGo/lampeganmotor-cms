<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\OtherAsset;
use App\Models\Sale;
use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    use InteractsWithPageFilters;

    /**
     * Set 3 card per baris
     */
    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        // Ambil filter dari pageFilters
        $startDate = ! is_null($this->pageFilters['startDate'] ?? null)
            ? Carbon::parse($this->pageFilters['startDate'])
            : Carbon::today();

        $endDate = ! is_null($this->pageFilters['endDate'] ?? null)
            ? Carbon::parse($this->pageFilters['endDate'])
            : Carbon::today();

        // =============================
        // Data
        // =============================

        // Stok unit tersedia
        $stokUnit = Vehicle::where('status', 'available')->count();

        // Terjual
        $terjualPeriode = Sale::whereBetween('sale_date', [$startDate, $endDate])->count();
        $terjualBulanIni = Sale::whereYear('sale_date', Carbon::now()->year)
            ->whereMonth('sale_date', Carbon::now()->month)
            ->count();
        $terjualTahunIni = Sale::whereYear('sale_date', Carbon::now()->year)->count();

        // Penjualan
        $totalPenjualanPeriode = Sale::whereBetween('sale_date', [$startDate, $endDate])->sum('sale_price');
        $totalPenjualanBulanIni = Sale::whereYear('sale_date', Carbon::now()->year)
            ->whereMonth('sale_date', Carbon::now()->month)
            ->sum('sale_price');
        $totalPenjualanTahunIni = Sale::whereYear('sale_date', Carbon::now()->year)->sum('sale_price');

        // Pengeluaran
        $totalPengeluaranPeriode = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $totalPengeluaranBulanIni = Expense::whereYear('expense_date', Carbon::now()->year)
            ->whereMonth('expense_date', Carbon::now()->month)
            ->sum('amount');
        $totalPengeluaranTahunIni = Expense::whereYear('expense_date', Carbon::now()->year)->sum('amount');

        // Aset
       // Kendaraan: hanya available + sesuai periode
        $totalAsetMotor = Vehicle::where('status', 'available')
            // ->whereBetween('expense_date', [$startDate, $endDate]) 
            ->sum('sale_price');

        // OtherAsset: sesuai periode
        $totalAsetLainnyaPeriode = OtherAsset::whereBetween('acquisition_date', [$startDate, $endDate])
            ->sum('value');

        $totalAsetLainnya = OtherAsset::sum('value');

        // Total
        $totalNilaiAset = $totalAsetMotor + $totalAsetLainnya;
        // =============================
        // Stats
        // =============================
        return [
            // ================= STOK =================
            Stat::make('Stok Unit', $stokUnit)
                ->description('Total unit tersedia')
                ->color('primary'),

            // ================= TERJUAL =================
            Stat::make('Terjual (Periode)', $terjualPeriode . ' Unit')
                ->description("{$startDate->format('d M Y')} s/d {$endDate->format('d M Y')}")
                ->color('success'),

            Stat::make('Terjual (Bulan Ini)', $terjualBulanIni . ' Unit')
                ->description('Jumlah unit terjual bulan ini')
                ->color('success'),

            // Stat::make('Terjual (Tahun Ini)', $terjualTahunIni . ' Unit')
            //     ->description('Jumlah unit terjual tahun ini')
            //     ->color('success'),

            // ================= PENJUALAN =================
            Stat::make('Penjualan (Periode)', 'Rp ' . number_format($totalPenjualanPeriode, 0, ',', '.'))
                ->description("{$startDate->format('d M Y')} s/d {$endDate->format('d M Y')}")
                ->color('warning'),

            Stat::make('Penjualan (Bulan Ini)', 'Rp ' . number_format($totalPenjualanBulanIni, 0, ',', '.'))
                ->description('Akumulasi penjualan bulan ini')
                ->color('warning'),

            Stat::make('Penjualan (Tahun Ini)', 'Rp ' . number_format($totalPenjualanTahunIni, 0, ',', '.'))
                ->description('Akumulasi penjualan tahun ini')
                ->color('warning'),

            // ================= PENGELUARAN =================
            Stat::make('Pengeluaran (Periode)', 'Rp ' . number_format($totalPengeluaranPeriode, 0, ',', '.'))
                ->description("{$startDate->format('d M Y')} s/d {$endDate->format('d M Y')}")
                ->color('danger'),

            Stat::make('Pengeluaran (Bulan Ini)', 'Rp ' . number_format($totalPengeluaranBulanIni, 0, ',', '.'))
                ->description('Total pengeluaran bulan ini')
                ->color('danger'),

            Stat::make('Pengeluaran (Tahun Ini)', 'Rp ' . number_format($totalPengeluaranTahunIni, 0, ',', '.'))
                ->description('Total pengeluaran tahun ini')
                ->color('danger'),

            // ================= ASET =================
            Stat::make('Total Aset Motor', 'Rp ' . number_format($totalAsetMotor, 0, ',', '.'))
                ->description('Nilai seluruh aset kendaraan')
                ->color('info'),

            Stat::make('Total Aset Lainnya', 'Rp ' . number_format($totalAsetLainnyaPeriode, 0, ',', '.'))
                ->description("{$startDate->format('d M Y')} s/d {$endDate->format('d M Y')}")
                ->color('info'),

            Stat::make('Total Nilai Aset', 'Rp ' . number_format($totalNilaiAset, 0, ',', '.'))
                ->description('Akumulasi semua aset')
                ->color('primary'),
        ];
    }
}
