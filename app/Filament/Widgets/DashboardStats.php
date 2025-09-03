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

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $startDate = isset($this->pageFilters['startDate']) && $this->pageFilters['startDate']
            ? Carbon::parse($this->pageFilters['startDate'])
            : Carbon::today();

        $endDate = isset($this->pageFilters['endDate']) && $this->pageFilters['endDate']
            ? Carbon::parse($this->pageFilters['endDate'])
            : Carbon::today();

        // ================= DATA =================
        $stokUnit = Vehicle::where('status', 'available')->count();

        $terjualPeriode = Sale::whereBetween('sale_date', [$startDate, $endDate])->count();
        $terjualBulanIni = Sale::whereYear('sale_date', Carbon::now()->year)
            ->whereMonth('sale_date', Carbon::now()->month)
            ->count();
        $terjualTahunIni = Sale::whereYear('sale_date', Carbon::now()->year)->count();

        $totalPenjualanPeriode = Sale::whereBetween('sale_date', [$startDate, $endDate])->sum('sale_price');
        $totalPenjualanBulanIni = Sale::whereYear('sale_date', Carbon::now()->year)
            ->whereMonth('sale_date', Carbon::now()->month)
            ->sum('sale_price');
        $totalPenjualanTahunIni = Sale::whereYear('sale_date', Carbon::now()->year)->sum('sale_price');

        $totalPengeluaranPeriode = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $totalPengeluaranBulanIni = Expense::whereYear('expense_date', Carbon::now()->year)
            ->whereMonth('expense_date', Carbon::now()->month)
            ->sum('amount');
        $totalPengeluaranTahunIni = Expense::whereYear('expense_date', Carbon::now()->year)->sum('amount');

        $totalAsetMotor = Vehicle::where('status', 'available')->sum('sale_price');
        $totalAsetLainnya = OtherAsset::sum('value');
        $totalNilaiAset = $totalAsetMotor + $totalAsetLainnya;

        // ================= STATS =================
        return [
            Stat::make(__('dashboard.stock_unit'), $stokUnit)
                ->description(__('dashboard.total_available_units'))
                ->color('primary'),

            Stat::make(__('dashboard.sold_period'), $terjualPeriode . ' ' . __('dashboard.unit'))
                ->description("{$startDate->format('d M Y')} s/d {$endDate->format('d M Y')}")
                ->color('success'),

            Stat::make(__('dashboard.sold_this_month'), $terjualBulanIni . ' ' . __('dashboard.unit'))
                ->description(__('dashboard.units_sold_this_month'))
                ->color('success'),

            Stat::make(__('dashboard.sales_period'), 'Rp ' . number_format($totalPenjualanPeriode, 0, ',', '.'))
                ->description("{$startDate->format('d M Y')} s/d {$endDate->format('d M Y')}")
                ->color('warning'),

            Stat::make(__('dashboard.sales_this_month'), 'Rp ' . number_format($totalPenjualanBulanIni, 0, ',', '.'))
                ->description(__('dashboard.accumulated_sales_this_month'))
                ->color('warning'),

            Stat::make(__('dashboard.sales_this_year'), 'Rp ' . number_format($totalPenjualanTahunIni, 0, ',', '.'))
                ->description(__('dashboard.accumulated_sales_this_year'))
                ->color('warning'),

            Stat::make(__('dashboard.expenses_period'), 'Rp ' . number_format($totalPengeluaranPeriode, 0, ',', '.'))
                ->description("{$startDate->format('d M Y')} s/d {$endDate->format('d M Y')}")
                ->color('danger'),

            Stat::make(__('dashboard.expenses_this_month'), 'Rp ' . number_format($totalPengeluaranBulanIni, 0, ',', '.'))
                ->description(__('dashboard.total_expenses_this_month'))
                ->color('danger'),

            Stat::make(__('dashboard.expenses_this_year'), 'Rp ' . number_format($totalPengeluaranTahunIni, 0, ',', '.'))
                ->description(__('dashboard.total_expenses_this_year'))
                ->color('danger'),

            Stat::make(__('dashboard.total_motor_assets'), 'Rp ' . number_format($totalAsetMotor, 0, ',', '.'))
                ->description(__('dashboard.value_all_vehicle_assets'))
                ->color('info'),

            Stat::make(__('dashboard.total_other_assets'), 'Rp ' . number_format($totalAsetLainnya, 0, ',', '.'))
                ->description(__('dashboard.total_all_other_assets'))
                ->color('info'),

            Stat::make(__('dashboard.total_asset_value'), 'Rp ' . number_format($totalNilaiAset, 0, ',', '.'))
                ->description(__('dashboard.accumulation_all_assets'))
                ->color('primary'),
        ];
    }
}
