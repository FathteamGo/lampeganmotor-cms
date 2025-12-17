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
        $month = isset($this->filters['month']) && $this->filters['month']
            ? (int)$this->filters['month']
            : now()->month;

        $year = isset($this->filters['year']) && $this->filters['year']
            ? (int)$this->filters['year']
            : now()->year;

        if ($month < 1 || $month > 12) $month = now()->month;
        if ($year < 2000 || $year > now()->year + 5) $year = now()->year;

        $rupiah = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
        $periode = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
        $totalUnit = Vehicle::where('status', 'available')->count();
        $totalUnitTerjual = Sale::valid()->whereYear('sale_date', $year)->count();

        // ========== PENJUALAN ==========
        $salesQuery = Sale::with('vehicle')->valid()->whereYear('sale_date', $year);
        $terjualBulanIni = (clone $salesQuery)->whereMonth('sale_date', $month)->count();
        $terjualTahunIni = (clone $salesQuery)->count();

        // Ambil data sales untuk dihitung labanya
        $salesBulanIni = (clone $salesQuery)->whereMonth('sale_date', $month)->get();
        $salesTahunIni = (clone $salesQuery)->get();

        // HITUNG LABA PENJUALAN
        $labaPenjualanBulanIni = 0;
        $labaPenjualanTahunIni = 0;

        // Hitung Laba Bulan Ini
        foreach ($salesBulanIni as $sale) {
            if (!$sale->vehicle) continue;

            $otr = floatval($sale->sale_price ?? 0);
            $dpPo = floatval($sale->dp_po ?? 0);
            $dpReal = floatval($sale->dp_real ?? 0);
            $purchasePrice = floatval($sale->vehicle->purchase_price ?? 0);

            switch ($sale->payment_method) {
                case 'credit':
                    // Kredit: OTR - DP PO - DP REAL - Harga Pembelian
                    $labaPenjualanBulanIni += ($otr - $dpPo + $dpReal - $purchasePrice);
                    break;

                case 'cash':
                    // Cash: OTR - Harga Pembelian
                    $labaPenjualanBulanIni += ($otr - $purchasePrice);
                    break;

                case 'cash_tempo':
                    // Cash Tempo: OTR - Harga Pembelian
                    $labaPenjualanBulanIni += ($otr - $purchasePrice);
                    break;

                case 'dana_tunai':
                    // Dana Tunai: OTR - DP PO - Pembayaran ke Nasabah
                    $paymentToCustomer = floatval($sale->payment_to_customer ?? 0);
                    $labaPenjualanBulanIni += ($otr - $dpPo - $paymentToCustomer);
                    break;

                case 'tukartambah':
                    // Tukar Tambah: OTR - Harga Pembelian
                    $labaPenjualanBulanIni += ($otr - $purchasePrice);
                    break;
            }
        }

        // Hitung Laba Tahun Ini
        foreach ($salesTahunIni as $sale) {
            if (!$sale->vehicle) continue;

            $otr = floatval($sale->sale_price ?? 0);
            $dpPo = floatval($sale->dp_po ?? 0);
            $dpReal = floatval($sale->dp_real ?? 0);
            $purchasePrice = floatval($sale->vehicle->purchase_price ?? 0);

            switch ($sale->payment_method) {
                case 'credit':
                    $labaPenjualanTahunIni += ($otr - $dpPo - $dpReal - $purchasePrice);
                    break;

                case 'cash':
                    $labaPenjualanTahunIni += ($otr - $purchasePrice);
                    break;

                case 'cash_tempo':
                    $labaPenjualanTahunIni += ($otr - $purchasePrice);
                    break;

                case 'dana_tunai':
                    $paymentToCustomer = floatval($sale->payment_to_customer ?? 0);
                    $labaPenjualanTahunIni += ($otr - $dpPo - $paymentToCustomer);
                    break;

                case 'tukartambah':
                    $labaPenjualanTahunIni += ($otr - $purchasePrice);
                    break;
            }
        }

        // ========== CASH TEMPO TRACKING ==========
        $cashTempoQuery = Sale::with('vehicle')->valid()
            ->where('payment_method', 'cash_tempo')
            ->where('remaining_payment', '>', 0)
            ->whereYear('sale_date', $year);

        $cashTempoBulanIni = (clone $cashTempoQuery)
            ->whereMonth('sale_date', $month)
            ->sum('remaining_payment');

        $cashTempoTahunIni = (clone $cashTempoQuery)->sum('remaining_payment');

        $totalCashTempoTransaksi = (clone $cashTempoQuery)->count();

        // Cash Tempo Jatuh Tempo 30 hari
        $cashTempoJatuhTempo = Sale::valid()
            ->where('payment_method', 'cash_tempo')
            ->where('remaining_payment', '>', 0)
            ->whereNotNull('due_date')
            ->where('due_date', '<=', now()->addDays(30))
            ->sum('remaining_payment');

        // ========== INCOME ==========
        $incomeQuery = Income::whereYear('income_date', $year);
        $totalIncomeBulanIni = (clone $incomeQuery)->whereMonth('income_date', $month)->sum('amount');
        $totalIncomeTahunIni = (clone $incomeQuery)->sum('amount');

        // ========== STNK ==========
        $stnkQuery = StnkRenewal::whereYear('tgl', $year);

        $stnkIncomeBulanIni = (clone $stnkQuery)
            ->whereMonth('tgl', $month)
            ->sum('margin_total');
        $stnkIncomeTahunIni = (clone $stnkQuery)->sum('margin_total');

        $stnkExpenseBulanIni = (clone $stnkQuery)
            ->whereMonth('tgl', $month)
            ->sum('payvendor');
        $stnkExpenseTahunIni = (clone $stnkQuery)->sum('payvendor');

        // ========== PENGELUARAN ==========
        $expenseQuery = Expense::whereYear('expense_date', $year);
        $totalPengeluaranBulanIni =
            (clone $expenseQuery)->whereMonth('expense_date', $month)->sum('amount')
            + $stnkExpenseBulanIni;

        $totalPengeluaranTahunIni =
            (clone $expenseQuery)->sum('amount')
            + $stnkExpenseTahunIni;

        // ========== LABA BERSIH ==========
        // Rumus: Laba Penjualan + Income + Income STNK - Pengeluaran
        $labaBersihBulanIni = $labaPenjualanBulanIni + $totalIncomeBulanIni + $stnkIncomeBulanIni - $totalPengeluaranBulanIni;
        $labaBersihTahunIni = $labaPenjualanTahunIni + $totalIncomeTahunIni + $stnkIncomeTahunIni - $totalPengeluaranTahunIni;

        // ========== ASET ==========
        $asetKendaraan = Vehicle::with('additionalCosts')
            ->whereIn('status', ['available', 'in_repair'])
            ->get()
            ->sum(fn ($v) =>
                floatval($v->purchase_price ?? 0)
                + floatval($v->additionalCosts?->sum('price') ?? 0)
            );

        $asetLainnya = OtherAsset::sum('value');
        $totalAset = $asetKendaraan + $asetLainnya;
        // ========== RETURN STATISTICS ==========
        return [
            // UNIT
            Stat::make('Total Unit Tersedia', "{$totalUnit} unit")
                ->description('Jumlah seluruh kendaraan tersedia')
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('primary')
                ->url(route('filament.admin.resources.vehicles.index')),

            Stat::make('Total Unit Terjual', "{$totalUnitTerjual} unit")
                ->description('Jumlah seluruh kendaraan terjual')
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('primary')
                ->url(route('filament.admin.resources.sales.index')),

            // PENJUALAN
            Stat::make('Terjual Bulan Ini', "{$terjualBulanIni} unit")
                ->description("Unit terjual pada {$periode}")
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('success')
                ->url(route('filament.admin.resources.sales.index')),

            Stat::make('Terjual Tahun Ini', "{$terjualTahunIni} unit")
                ->description("Total unit terjual sepanjang {$year}")
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('success')
                ->url(route('filament.admin.resources.sales.index')),

            // LABA PENJUALAN
            Stat::make('Laba Penjualan Bulan Ini', $rupiah($labaPenjualanBulanIni))
                ->description("Laba kotor penjualan {$periode}")
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('warning')
                ->url(route('filament.admin.resources.sales.index')),

            Stat::make('Laba Penjualan Tahun Ini', $rupiah($labaPenjualanTahunIni))
                ->description("Akumulasi laba penjualan {$year}")
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('warning')
                ->url(route('filament.admin.resources.sales.index')),

            // CASH TEMPO TRACKING
            Stat::make('Cash Tempo Mengendap', $rupiah($cashTempoTahunIni))
                ->description("Sisa transaksi belum lunas ({$totalCashTempoTransaksi} transaksi)")
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('warning')
                ->url(route('filament.admin.resources.cash-tempo-trackings.index')),
    
            Stat::make('Cash Tempo Jatuh Tempo', $rupiah($cashTempoJatuhTempo))
                ->description("Akan jatuh tempo dalam 30 hari")
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('danger')
                ->url(route('filament.admin.resources.cash-tempo-trackings.index')),

            // INCOME
            Stat::make('Income Bulan Ini', $rupiah($totalIncomeBulanIni))
                ->description("Pendapatan tambahan {$periode}")
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('info')
                ->url(route('filament.admin.resources.incomes.index')),

            Stat::make('Income Tahun Ini', $rupiah($totalIncomeTahunIni))
                ->description("Pendapatan tambahan {$year}")
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('info')
                ->url(route('filament.admin.resources.incomes.index')),

            Stat::make('Income STNK Bulan Ini', $rupiah($stnkIncomeBulanIni))
                ->description("Pendapatan STNK {$periode}")
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('info')
                ->url(route('filament.admin.resources.stnk-renewals.index')),

            Stat::make('Income STNK Tahun Ini', $rupiah($stnkIncomeTahunIni))
                ->description("Pendapatan STNK {$year}")
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('info')
                ->url(route('filament.admin.resources.stnk-renewals.index')),

            // PENGELUARAN
            Stat::make('Pengeluaran Bulan Ini', $rupiah($totalPengeluaranBulanIni))
                ->description("Total biaya keluar {$periode} sudah termasuk biaya lain")
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('danger')
                ->url(route('filament.admin.resources.expenses.index')),

            Stat::make('Pengeluaran Tahun Ini', $rupiah($totalPengeluaranTahunIni))
                ->description("Total pengeluaran {$year}")
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('danger')
                ->url(route('filament.admin.resources.expenses.index')),

            // LABA BERSIH
            Stat::make('Laba Bersih Bulan Ini', $rupiah($labaBersihBulanIni))
                ->description("Laba - Pengeluaran {$periode}")
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color($labaBersihBulanIni >= 0 ? 'success' : 'danger')
                ->url(route('filament.admin.resources.sales.index')),

            Stat::make('Laba Bersih Tahun Ini', $rupiah($labaBersihTahunIni))
                ->description("Laba - Pengeluaran {$year}")
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color($labaBersihTahunIni >= 0 ? 'success' : 'danger')
                ->url(route('filament.admin.resources.sales.index')),

            // ASET
            Stat::make('Aset Kendaraan', $rupiah($asetKendaraan))
                ->description('Total nilai pembelian kendaraan tersedia')
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('primary')
                ->url(route('filament.admin.resources.vehicles.index')),

            Stat::make('Aset Lainnya', $rupiah($asetLainnya))
                ->description('Nilai aset non-kendaraan')
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('primary')
                ->url(route('filament.admin.resources.other-assets.index')),

            Stat::make('Total Aset Keseluruhan', $rupiah($totalAset))
                ->description('Akumulasi nilai seluruh aset')
                ->descriptionIcon('heroicon-o-arrow-right-circle')
                ->color('primary')
                ->url(route('filament.admin.resources.other-assets.index')),
        ];
    }
}