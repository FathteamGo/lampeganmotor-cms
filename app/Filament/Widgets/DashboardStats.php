<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Income;
use App\Models\OtherAsset;
use App\Models\Sale;
use App\Models\StnkRenewal;
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
        // ====== BACA FILTER DENGAN DEFAULT SEKARANG ======
        $month = isset($this->filters['month']) && $this->filters['month'] ? (int)$this->filters['month'] : now()->month;
        $year  = isset($this->filters['year']) && $this->filters['year'] ? (int)$this->filters['year'] : now()->year;

        // Validasi aman
        if ($month < 1 || $month > 12) $month = now()->month;
        if ($year < 2000 || $year > now()->year + 5) $year = now()->year;

        // Format rupiah
        $rupiah = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');

        // Format periode yang aman
        $periode = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

        //Unit
        $totalUnit = Vehicle::count();

        // ================= PENJUALAN =================
        $salesQuery = Sale::valid()->whereYear('sale_date', $year);
        $terjualBulanIni = (clone $salesQuery)->whereMonth('sale_date', $month)->count();
        $terjualTahunIni = (clone $salesQuery)->count();
        $totalPenjualanBulanIni = (clone $salesQuery)->whereMonth('sale_date', $month)->sum('sale_price');
        $totalPenjualanTahunIni = (clone $salesQuery)->sum('sale_price');

        // ================= INCOME =================
        $incomeQuery = Income::whereYear('income_date', $year);
        $totalIncomeBulanIni = (clone $incomeQuery)->whereMonth('income_date', $month)->sum('amount');
        $totalIncomeTahunIni = (clone $incomeQuery)->sum('amount');

        // ================= STNK =================
        $stnkQuery = StnkRenewal::whereYear('tgl', $year);
        $stnkIncomeBulanIni = (clone $stnkQuery)->whereMonth('tgl', $month)->sum('margin_total');
        $stnkIncomeTahunIni = (clone $stnkQuery)->sum('margin_total');
        $stnkExpenseBulanIni = (clone $stnkQuery)->whereMonth('tgl', $month)->sum('pembayaran_ke_samsat');
        $stnkExpenseTahunIni = (clone $stnkQuery)->sum('pembayaran_ke_samsat');

        // ================= PENGELUARAN =================
        $expenseQuery = Expense::whereYear('expense_date', $year);
        $totalPengeluaranBulanIni = (clone $expenseQuery)->whereMonth('expense_date', $month)->sum('amount') + $stnkExpenseBulanIni;
        $totalPengeluaranTahunIni = (clone $expenseQuery)->sum('amount') + $stnkExpenseTahunIni;

        // ================= KEUNTUNGAN =================
        $totalPendapatanBulanIni = $totalPenjualanBulanIni + $totalIncomeBulanIni + $stnkIncomeBulanIni;
        $totalPendapatanTahunIni = $totalPenjualanTahunIni + $totalIncomeTahunIni + $stnkIncomeTahunIni;
        $keuntunganBulanIni = $totalPendapatanBulanIni - $totalPengeluaranBulanIni;
        $keuntunganTahunIni = $totalPendapatanTahunIni - $totalPengeluaranTahunIni;

        // ================= ASET =================
        $asetKendaraan = Vehicle::where('status', 'available')->sum('sale_price');
        $asetLainnya = OtherAsset::sum('value');
        $totalAset = $asetKendaraan + $asetLainnya;

        // ================= RETURN =================
        return [
            // UNIT
            Stat::make('Total Unit Tersedia', "{$totalUnit} unit")
                ->description('Jumlah seluruh kendaraan tersedia')
                ->color('primary'),
            // PENJUALAN
            Stat::make('Terjual Bulan Ini', "{$terjualBulanIni} unit")
                ->description("Unit terjual pada {$periode}")
                ->color('success'),

            Stat::make('Terjual Tahun Ini', "{$terjualTahunIni} unit")
                ->description("Total unit terjual sepanjang {$year}")
                ->color('success'),

            Stat::make('Total Penjualan Bulan Ini', $rupiah($totalPenjualanBulanIni))
                ->description("Nominal penjualan bulan {$periode}")
                ->color('warning'),

            Stat::make('Total Penjualan Tahun Ini', $rupiah($totalPenjualanTahunIni))
                ->description("Akumulasi penjualan sepanjang {$year}")
                ->color('warning'),

            // INCOME
            Stat::make('Income Bulan Ini', $rupiah($totalIncomeBulanIni))
                ->description("Pendapatan tambahan bulan {$periode}")
                ->color('info'),

            Stat::make('Income Tahun Ini', $rupiah($totalIncomeTahunIni))
                ->description("Pendapatan tambahan sepanjang {$year}")
                ->color('info'),

            Stat::make('Income STNK Bulan Ini', $rupiah($stnkIncomeBulanIni))
                ->description("Pendapatan dari STNK bulan {$periode}")
                ->color('info'),

            Stat::make('Income STNK Tahun Ini', $rupiah($stnkIncomeTahunIni))
                ->description("Pendapatan STNK sepanjang {$year}")
                ->color('info'),

            // PENGELUARAN
            Stat::make('Pengeluaran Bulan Ini', $rupiah($totalPengeluaranBulanIni))
                ->description("Biaya keluar {$periode} (termasuk STNK)")
                ->color('danger'),

            Stat::make('Pengeluaran Tahun Ini', $rupiah($totalPengeluaranTahunIni))
                ->description("Total pengeluaran sepanjang {$year}")
                ->color('danger'),

            // KEUNTUNGAN
            Stat::make('Keuntungan Bulan Ini', $rupiah($keuntunganBulanIni))
                ->description("Pendapatan bersih bulan {$periode}")
                ->color($keuntunganBulanIni >= 0 ? 'success' : 'danger'),

            Stat::make('Keuntungan Tahun Ini', $rupiah($keuntunganTahunIni))
                ->description("Pendapatan bersih tahun {$year}")
                ->color($keuntunganTahunIni >= 0 ? 'success' : 'danger'),

            // ASET
            Stat::make('Aset Kendaraan', $rupiah($asetKendaraan))
                ->description('Total nilai kendaraan tersedia')
                ->color('primary'),

            Stat::make('Aset Lainnya', $rupiah($asetLainnya))
                ->description('Nilai aset non-kendaraan')
                ->color('primary'),

            Stat::make('Total Aset Keseluruhan', $rupiah($totalAset))
                ->description('Akumulasi nilai seluruh aset')
                ->color('primary'),
        ];
    }
}
