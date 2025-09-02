<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\OtherAsset;
use App\Models\Sale;
use App\Models\Vehicle;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected ?string $pollingInterval = null;

    public ?string $startDate = null;
    public ?string $endDate = null;

    protected $listeners = ['filterChanged' => 'applyFilter'];

    public function applyFilter($startDate, $endDate): void
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    protected function getStats(): array
    {
        // Default filter 
        $start = $this->startDate ?? Carbon::now()->startOfMonth()->toDateString();
        $end   = $this->endDate ?? Carbon::now()->endOfMonth()->toDateString();

        // Data stok unit tersedia
        $stokUnit = Vehicle::where('status', 'available')->count();

        // Jumlah unit terjual
        $terjualHariIni = Sale::whereDate('sale_date', Carbon::today())->count();
        $terjualBulanIni = Sale::whereYear('sale_date', Carbon::now()->year)
            ->whereMonth('sale_date', Carbon::now()->month)
            ->count();

        // Jumlah unit terjual filter
        $terjualFilter = Sale::whereBetween('sale_date', [$start, $end])->count();

        // Total penjualan (uang)
        $totalPenjualanHariIni = Sale::whereDate('sale_date', Carbon::today())->sum('sale_price');
        $totalPenjualanBulanIni = Sale::whereYear('sale_date', Carbon::now()->year)
            ->whereMonth('sale_date', Carbon::now()->month)
            ->sum('sale_price');
        $totalPenjualanTahunIni = Sale::whereYear('sale_date', Carbon::now()->year)->sum('sale_price');

        // Total penjualan filter
        $totalPenjualanFilter = Sale::whereBetween('sale_date', [$start, $end])->sum('sale_price');

        // Total pengeluaran (uang)
        $totalPengeluaranHariIni = Expense::whereDate('expense_date', Carbon::today())->sum('amount');
        $totalPengeluaranBulanIni = Expense::whereYear('expense_date', Carbon::now()->year)
            ->whereMonth('expense_date', Carbon::now()->month)
            ->sum('amount');
        $totalPengeluaranTahunIni = Expense::whereYear('expense_date', Carbon::now()->year)->sum('amount');

        // Total pengeluaran filter
        $totalPengeluaranFilter = Expense::whereBetween('expense_date', [$start, $end])->sum('amount');

        // Total nilai aset
        $totalAsetMotor = Vehicle::sum('purchase_price');
        $totalAsetLainnya = OtherAsset::sum('value');
        $totalNilaiAset = $totalAsetMotor + $totalAsetLainnya;

        $stats = [
            Stat::make('Stok', $stokUnit)
                ->description('Total unit tersedia')
                ->color('primary'),

            Stat::make('Terjual Hari Ini', $terjualHariIni . ' Unit')
                ->description('Jumlah unit terjual hari ini')
                ->color('success'),

            Stat::make('Terjual Bulan Ini', $terjualBulanIni . ' Unit')
                ->description('Jumlah unit terjual bulan ini')
                ->color('success'),

            Stat::make('Total Penjualan Hari Ini', 'Rp ' . number_format($totalPenjualanHariIni, 0, ',', '.'))
                ->description('Akumulasi penjualan hari ini')
                ->color('warning'),

            Stat::make('Total Penjualan Bulan Ini', 'Rp ' . number_format($totalPenjualanBulanIni, 0, ',', '.'))
                ->description('Akumulasi penjualan bulan ini')
                ->color('warning'),

            Stat::make('Total Penjualan Tahun Ini', 'Rp ' . number_format($totalPenjualanTahunIni, 0, ',', '.'))
                ->description('Akumulasi penjualan tahun ini')
                ->color('warning'),

            Stat::make('Pengeluaran Hari Ini', 'Rp ' . number_format($totalPengeluaranHariIni, 0, ',', '.'))
                ->description('Total pengeluaran hari ini')
                ->color('danger'),

            Stat::make('Pengeluaran Bulan Ini', 'Rp ' . number_format($totalPengeluaranBulanIni, 0, ',', '.'))
                ->description('Total pengeluaran bulan ini')
                ->color('danger'),

            Stat::make('Pengeluaran Tahun Ini', 'Rp ' . number_format($totalPengeluaranTahunIni, 0, ',', '.'))
                ->description('Total pengeluaran tahun ini')
                ->color('danger'),

            Stat::make('Total Aset Motor', 'Rp ' . number_format($totalAsetMotor, 0, ',', '.'))
                ->description('Nilai seluruh aset kendaraan')
                ->color('info'),

            Stat::make('Total Aset Lainnya', 'Rp ' . number_format($totalAsetLainnya, 0, ',', '.'))
                ->description('Nilai aset non-kendaraan')
                ->color('info'),

            Stat::make('Total Nilai Aset', 'Rp ' . number_format($totalNilaiAset, 0, ',', '.'))
                ->description('Akumulasi semua aset')
                ->color('primary'),
        ];

      
        if ($this->startDate && $this->endDate) {
            $stats[] = Stat::make('Terjual (Filter)', $terjualFilter . ' Unit')
                ->description("Dari {$start} s/d {$end}")
                ->color('success');

            $stats[] = Stat::make('Total Penjualan (Filter)', 'Rp ' . number_format($totalPenjualanFilter, 0, ',', '.'))
                ->description("Dari {$start} s/d {$end}")
                ->color('warning');

            $stats[] = Stat::make('Total Pengeluaran (Filter)', 'Rp ' . number_format($totalPengeluaranFilter, 0, ',', '.'))
                ->description("Dari {$start} s/d {$end}")
                ->color('danger');
        }

        return $stats;
    }
}
