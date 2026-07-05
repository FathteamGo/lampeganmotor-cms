<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Vehicle;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Customer;
use App\Models\WeeklyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MaryamController extends Controller
{
    /**
     * GET /api/maryam/dashboard
     * Ringkasan dashboard untuk CS
     */
    public function dashboard(Request $request)
    {

        $today = Carbon::now();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        // Minggu ini
        $salesWeek = Sale::whereBetween('sale_date', [$startOfWeek, $endOfWeek])
            ->where('status', '!=', 'cancel')
            ->count();
        $revenueWeek = Sale::whereBetween('sale_date', [$startOfWeek, $endOfWeek])
            ->where('status', '!=', 'cancel')
            ->sum('sale_price');

        // Bulan ini
        $salesMonth = Sale::whereBetween('sale_date', [$startOfMonth, $endOfMonth])
            ->where('status', '!=', 'cancel')
            ->count();
        $revenueMonth = Sale::whereBetween('sale_date', [$startOfMonth, $endOfMonth])
            ->where('status', '!=', 'cancel')
            ->sum('sale_price');

        // Stok tersedia
        $stock = Vehicle::where('status', 'tersedia')->count();

        // Pemasukan & Pengeluaran bulan ini
        $incomeMonth = Income::whereBetween('income_date', [$startOfMonth, $endOfMonth])->sum('amount');
        $expenseMonth = Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])->sum('amount');
        $profitMonth = $incomeMonth - $expenseMonth;

        return response()->json([
            'success' => true,
            'periode' => [
                'minggu' => $startOfWeek->format('d M') . ' - ' . $endOfWeek->format('d M Y'),
                'bulan' => $startOfMonth->format('F Y'),
            ],
            'penjualan_minggu' => [
                'jumlah' => $salesWeek,
                'total' => (float) $revenueWeek,
            ],
            'penjualan_bulan' => [
                'jumlah' => $salesMonth,
                'total' => (float) $revenueMonth,
            ],
            'keuangan_bulan' => [
                'pemasukan' => (float) $incomeMonth,
                'pengeluaran' => (float) $expenseMonth,
                'laba_bersih' => (float) $profitMonth,
            ],
            'stok_tersedia' => $stock,
        ]);
    }

    /**
     * GET /api/maryam/sales
     * Daftar penjualan (filterable)
     */
    public function sales(Request $request)
    {
        $query = Sale::where('status', '!=', 'cancel')
            ->with(['vehicle.vehicleModel.brand', 'customer'])
            ->orderByDesc('sale_date');

        // Filter by date range
        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('sale_date', [$request->from, $request->to]);
        } elseif ($request->has('days')) {
            $query->where('sale_date', '>=', Carbon::now()->subDays($request->days));
        }

        $sales = $query->limit(50)->get();

        return response()->json([
            'success' => true,
            'count' => $sales->count(),
            'data' => $sales->map(fn($s) => [
                'id' => $s->id,
                'tanggal' => $s->sale_date?->format('d M Y'),
                'motor' => $s->vehicle?->vehicleModel?->brand?->name . ' ' . $s->vehicle?->vehicleModel?->name,
                'warna' => $s->vehicle?->color?->name,
                'harga_jual' => (float) $s->sale_price,
                'pembeli' => $s->customer?->name,
                'metode_bayar' => $s->payment_method,
                'status' => $s->status,
                'laba_bersih' => (float) $s->laba_bersih,
            ]),
        ]);
    }

    /**
     * GET /api/maryam/vehicles
     * Stok motor tersedia
     */
    public function vehicles(Request $request)
    {
        $query = Vehicle::where('status', 'tersedia')
            ->with(['vehicleModel.brand', 'color'])
            ->orderByDesc('created_at');

        if ($request->has('brand')) {
            $query->whereHas('vehicleModel.brand', fn($q) => $q->where('name', 'like', "%{$request->brand}%"));
        }

        $vehicles = $query->limit(50)->get();

        return response()->json([
            'success' => true,
            'count' => $vehicles->count(),
            'data' => $vehicles->map(fn($v) => [
                'id' => $v->id,
                'motor' => $v->vehicleModel?->brand?->name . ' ' . $v->vehicleModel?->name,
                'warna' => $v->color?->name,
                'tahun' => $v->year?->year,
                'harga_beli' => (float) $v->purchase_price,
                'harga_jual' => (float) $v->selling_price,
                'odometer' => $v->odometer,
                'status' => $v->status,
            ]),
        ]);
    }

    /**
     * GET /api/maryam/weekly-report
     * Laporan mingguan terakhir
     */
    public function weeklyReport(Request $request)
    {
        $report = WeeklyReport::orderByDesc('end_date')->first();

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada laporan mingguan',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'periode' => $report->start_date . ' - ' . $report->end_date,
                'pengunjung' => $report->visitors,
                'penjualan' => [
                    'jumlah' => $report->sales_count,
                    'total' => (float) $report->sales_total,
                ],
                'keuangan' => [
                    'pemasukan' => (float) $report->income_total,
                    'pengeluaran' => (float) $report->expense_total,
                    'laba_bersih' => (float) $report->total_income,
                ],
                'stok' => $report->stock,
                'stnk_renewal' => $report->stnk_renewal,
                'top_motors' => $report->top_motors,
                'insight' => $report->insight,
            ],
        ]);
    }

    /**
     * GET /api/maryam/weekly-summary
     * Ringkasan pekanan untuk cron report (formatted text)
     */
    public function weeklySummary(Request $request)
    {
        $today = Carbon::now();

        // Default: minggu lalu (Senin-Minggu)
        if ($today->isSunday()) {
            $start = $today->copy()->subWeek()->startOfWeek(Carbon::MONDAY);
            $end = $today->copy()->subDay()->endOfDay();
        } else {
            $start = $today->copy()->startOfWeek(Carbon::MONDAY);
            $end = $today->copy()->endOfDay();
        }

        $salesCount = Sale::whereBetween('sale_date', [$start, $end])
            ->where('status', '!=', 'cancel')->count();
        $salesTotal = Sale::whereBetween('sale_date', [$start, $end])
            ->where('status', '!=', 'cancel')->sum('sale_price');

        $income = Income::whereBetween('income_date', [$start, $end])->sum('amount');
        $expense = Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
        $profit = $income - $expense;

        $stock = Vehicle::where('status', 'tersedia')->count();

        // Top motor minggu ini
        $topMotors = Sale::whereBetween('sale_date', [$start, $end])
            ->where('status', '!=', 'cancel')
            ->select('vehicle_id', DB::raw('COUNT(*) as total'))
            ->groupBy('vehicle_id')
            ->with('vehicle.vehicleModel.brand')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(fn($r) => [
                'motor' => $r->vehicle?->vehicleModel?->brand?->name . ' ' . $r->vehicle?->vehicleModel?->name,
                'unit' => $r->total,
            ]);

        // Perbandingan minggu lalu
        $prevStart = $start->copy()->subWeek();
        $prevEnd = $end->copy()->subWeek();
        $prevSales = Sale::whereBetween('sale_date', [$prevStart, $prevEnd])
            ->where('status', '!=', 'cancel')->sum('sale_price');
        $prevIncome = Income::whereBetween('income_date', [$prevStart, $prevEnd])->sum('amount');
        $prevExpense = Expense::whereBetween('expense_date', [$prevStart, $prevEnd])->sum('amount');

        $salesDiff = $prevSales > 0 ? round((($salesTotal - $prevSales) / $prevSales) * 100, 1) : 0;
        $incomeDiff = $prevIncome > 0 ? round((($income - $prevIncome) / $prevIncome) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'periode' => $start->format('d M') . ' - ' . $end->format('d M Y'),
            'ringkasan' => [
                'penjualan_unit' => $salesCount,
                'penjualan_total' => (float) $salesTotal,
                'pemasukan' => (float) $income,
                'pengeluaran' => (float) $expense,
                'laba_bersih' => (float) $profit,
                'stok_tersedia' => $stock,
            ],
            'top_motors' => $topMotors,
            'perbandingan' => [
                'penjualan_vs_minggu_lalu' => $salesDiff > 0 ? "naik {$salesDiff}%" : ($salesDiff < 0 ? "turun " . abs($salesDiff) . "%" : "stabil"),
                'pemasukan_vs_minggu_lalu' => $incomeDiff > 0 ? "naik {$incomeDiff}%" : ($incomeDiff < 0 ? "turun " . abs($incomeDiff) . "%" : "stabil"),
            ],
        ]);
    }

}
