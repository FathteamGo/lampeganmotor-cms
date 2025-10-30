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
        $month = $this->filters['month'] ?? now()->month;
        $year  = $this->filters['year']  ?? now()->year;

        // Helper buat format rupiah
        $rupiah = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');

        // ==============================
        // PENJUALAN (hanya valid / != cancel)
        // ==============================
        $salesQuery = Sale::valid()->whereYear('sale_date', $year);

        $totalPenjualanBulanIni = (clone $salesQuery)
            ->whereMonth('sale_date', $month)
            ->sum('sale_price');

        $totalPenjualanTahunIni = (clone $salesQuery)
            ->sum('sale_price');

        $terjualBulanIni = (clone $salesQuery)
            ->whereMonth('sale_date', $month)
            ->count();

        $terjualTahunIni = (clone $salesQuery)->count();

        // ==============================
        // INCOME
        // ==============================
        $incomeQuery = Income::whereYear('income_date', $year);

        $totalIncomeBulanIni = (clone $incomeQuery)
            ->whereMonth('income_date', $month)
            ->sum('amount');

        $totalIncomeTahunIni = (clone $incomeQuery)->sum('amount');

        // ==============================
        // EXPENSE
        // ==============================
        $expenseQuery = Expense::whereYear('expense_date', $year);

        $totalPengeluaranBulanIni = (clone $expenseQuery)
            ->whereMonth('expense_date', $month)
            ->sum('amount');

        $totalPengeluaranTahunIni = (clone $expenseQuery)->sum('amount');

        // ==============================
        // KEUNTUNGAN
        // ==============================
        $totalPendapatanBulanIni = $totalPenjualanBulanIni + $totalIncomeBulanIni;
        $totalPendapatanTahunIni = $totalPenjualanTahunIni + $totalIncomeTahunIni;

        $keuntunganBulanIni = $totalPendapatanBulanIni - $totalPengeluaranBulanIni;
        $keuntunganTahunIni = $totalPendapatanTahunIni - $totalPengeluaranTahunIni;

        // ==============================
        // ASET
        // ==============================
        $totalAsetMotor = Vehicle::where('status', 'available')->sum('sale_price');
        $totalAsetLainnya = OtherAsset::sum('value');
        $totalNilaiAset = $totalAsetMotor + $totalAsetLainnya;

        // ==============================
        // STATS CARD
        // ==============================
        $periode = Carbon::create($year, $month)->translatedFormat('F Y');

        return [
            // ===== UNIT =====
            Stat::make('Stok Unit', Vehicle::where('status', 'available')->count())
                ->description('Total unit tersedia')
                ->color('primary'),

            Stat::make('Terjual Bulan Ini', "{$terjualBulanIni} unit")
                ->description("Penjualan bulan {$periode}")
                ->color('success'),

            Stat::make('Terjual Tahun Ini', "{$terjualTahunIni} unit")
                ->description("Total unit terjual sepanjang tahun {$year}")
                ->color('success'),

            // ===== BULANAN =====
            Stat::make('Penjualan Bulan Ini', $rupiah($totalPenjualanBulanIni))
                ->description("Total penjualan bulan {$periode}")
                ->color('warning'),

            Stat::make('Income Bulan Ini', $rupiah($totalIncomeBulanIni))
                ->description("Pendapatan tambahan bulan {$periode}")
                ->color('info'),

            Stat::make('Pengeluaran Bulan Ini', $rupiah($totalPengeluaranBulanIni))
                ->description("Total biaya keluar bulan {$periode}")
                ->color('danger'),

            Stat::make('Keuntungan Bulan Ini', $rupiah($keuntunganBulanIni))
                ->description("Pendapatan - Pengeluaran bulan {$periode}")
                ->color($keuntunganBulanIni >= 0 ? 'success' : 'danger'),

            // ===== TAHUNAN =====
            Stat::make('Penjualan Tahun Ini', $rupiah($totalPenjualanTahunIni))
                ->description("Total penjualan sepanjang tahun {$year}")
                ->color('warning'),

            Stat::make('Income Tahun Ini', $rupiah($totalIncomeTahunIni))
                ->description("Pendapatan tambahan sepanjang tahun {$year}")
                ->color('info'),

            Stat::make('Pengeluaran Tahun Ini', $rupiah($totalPengeluaranTahunIni))
                ->description("Total biaya keluar sepanjang tahun {$year}")
                ->color('danger'),

            Stat::make('Keuntungan Tahun Ini', $rupiah($keuntunganTahunIni))
                ->description("Akumulasi pendapatan bersih tahun {$year}")
                ->color($keuntunganTahunIni >= 0 ? 'success' : 'danger'),

            // ===== ASET =====
            Stat::make('Total Nilai Aset', $rupiah($totalNilaiAset))
                ->description('Aset motor + aset lainnya yang masih dimiliki')
                ->color('primary'),
        ];
    }
}
