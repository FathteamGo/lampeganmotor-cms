<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Income;
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

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        // ============ FILTER ===============
        $month = isset($this->filters['month'])
            ? (int)$this->filters['month']
            : now()->month;

        $year = isset($this->filters['year'])
            ? (int)$this->filters['year']
            : now()->year;

        // ============ PENJUALAN ============
        $totalPenjualanBulanIni = Sale::whereYear('sale_date', $year)
            ->whereMonth('sale_date', $month)
            ->sum('sale_price');

        $totalPenjualanTahunIni = Sale::whereYear('sale_date', $year)->sum('sale_price');

        $terjualBulanIni = Sale::whereYear('sale_date', $year)
            ->whereMonth('sale_date', $month)
            ->count();

        $terjualTahunIni = Sale::whereYear('sale_date', $year)->count();

        // ============ INCOME ============
        $totalIncomeBulanIni = Income::whereYear('income_date', $year)
            ->whereMonth('income_date', $month)
            ->sum('amount');

        $totalIncomeTahunIni = Income::whereYear('income_date', $year)->sum('amount');

        // ============ PENGELUARAN ============
        $totalPengeluaranBulanIni = Expense::whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->sum('amount');

        $totalPengeluaranTahunIni = Expense::whereYear('expense_date', $year)->sum('amount');

        // ============ TOTAL PENDAPATAN ============
        $totalPendapatanBulanIni = $totalPenjualanBulanIni + $totalIncomeBulanIni;
        $totalPendapatanTahunIni = $totalPenjualanTahunIni + $totalIncomeTahunIni;

        // ============ KEUNTUNGAN ============
        $keuntunganBulanIni = $totalPendapatanBulanIni - $totalPengeluaranBulanIni;
        $keuntunganTahunIni = $totalPendapatanTahunIni - $totalPengeluaranTahunIni;

        // ============ ASET ============
        $totalAsetMotor = Vehicle::where('status', 'available')->sum('sale_price');
        $totalAsetLainnya = OtherAsset::sum('value');
        $totalNilaiAset = $totalAsetMotor + $totalAsetLainnya;

        // ============ STATS CARD ============
        return [
            // ========== DATA UNIT ==========
            Stat::make('Stok Unit', Vehicle::where('status', 'available')->count())
                ->description('Total unit tersedia')
                ->color('primary'),

            Stat::make('Terjual Bulan Ini', "{$terjualBulanIni} unit")
                ->description('Penjualan bulan ' . Carbon::create($year, $month)->translatedFormat('F Y'))
                ->color('success'),

            Stat::make('Terjual Tahun Ini', "{$terjualTahunIni} unit")
                ->description("Total unit terjual sepanjang tahun {$year}")
                ->color('success'),

            // ========== DATA BULANAN ==========
            Stat::make('Penjualan Bulan Ini', 'Rp ' . number_format($totalPenjualanBulanIni, 0, ',', '.'))
                ->description('Total penjualan bulan ' . Carbon::create($year, $month)->translatedFormat('F Y'))
                ->color('warning'),

            Stat::make('Income Bulan Ini', 'Rp ' . number_format($totalIncomeBulanIni, 0, ',', '.'))
                ->description('Pendapatan tambahan bulan ' . Carbon::create($year, $month)->translatedFormat('F Y'))
                ->color('info'),

            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format($totalPengeluaranBulanIni, 0, ',', '.'))
                ->description('Total biaya keluar bulan ' . Carbon::create($year, $month)->translatedFormat('F Y'))
                ->color('danger'),

            Stat::make('Keuntungan Bulan Ini', 'Rp ' . number_format($keuntunganBulanIni, 0, ',', '.'))
                ->description('Pendapatan - Pengeluaran bulan ' . Carbon::create($year, $month)->translatedFormat('F Y'))
                ->color($keuntunganBulanIni >= 0 ? 'success' : 'danger'),

            // ========== DATA TAHUNAN ==========
            Stat::make('Penjualan Tahun Ini', 'Rp ' . number_format($totalPenjualanTahunIni, 0, ',', '.'))
                ->description("Total penjualan sepanjang tahun {$year}")
                ->color('warning'),

            Stat::make('Income Tahun Ini', 'Rp ' . number_format($totalIncomeTahunIni, 0, ',', '.'))
                ->description("Pendapatan tambahan sepanjang tahun {$year}")
                ->color('info'),

            Stat::make('Pengeluaran Tahun Ini', 'Rp ' . number_format($totalPengeluaranTahunIni, 0, ',', '.'))
                ->description("Total biaya keluar sepanjang tahun {$year}")
                ->color('danger'),

            Stat::make('Keuntungan Tahun Ini', 'Rp ' . number_format($keuntunganTahunIni, 0, ',', '.'))
                ->description("Akumulasi pendapatan bersih tahun {$year}")
                ->color($keuntunganTahunIni >= 0 ? 'success' : 'danger'),

            // ========== DATA ASET ==========
            Stat::make('Total Nilai Aset', 'Rp ' . number_format($totalNilaiAset, 0, ',', '.'))
                ->description('Aset motor + aset lainnya yang masih dimiliki')
                ->color('primary'),
        ];
    }
}
