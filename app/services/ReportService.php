<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\Sale;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Vehicle;
use App\Models\StnkRenewal;
use App\Models\weekly_reports;
use App\Models\WeeklyReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\GeminiService;

class ReportService
{
    public function generateWeeklyReport(): array
    {
        $start = now()->subDays(7)->startOfDay();
        $end   = now()->subDay()->endOfDay();

        $pengunjung = DB::table('visitors')
            ->whereBetween('visited_at', [$start, $end])
            ->count();

        $penjualanJumlah = Sale::whereBetween('sale_date', [$start, $end])->count();
        $penjualanTotal  = Sale::whereBetween('sale_date', [$start, $end])->sum('sale_price');

        $pemasukan     = Income::whereBetween('income_date', [$start, $end])->sum('amount');
        $pengeluaran   = Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
        $saldo         = $pemasukan - $pengeluaran;

        $stok = Vehicle::doesntHave('sale')->count();

        $stnk = StnkRenewal::whereBetween('tgl', [$start, $end])->count();

        return [
            'periode' => [
                'mulai' => $start->toDateString(),
                'selesai' => $end->toDateString(),
            ],
            'pengunjung' => $pengunjung,
            'penjualan' => [
                'jumlah' => $penjualanJumlah,
                'total' => $penjualanTotal,
            ],
            'keuangan' => [
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
                'saldo' => $saldo,
            ],
            'stok' => $stok,
            'perpanjangan_stnk' => $stnk,
        ];
    }

    public function saveWeeklyReport(GeminiService $gemini): WeeklyReport
    {
        $data = $this->generateWeeklyReport();

        // Motor terlaris 5 besar
        $bestSelling = Sale::whereBetween('sale_date', [$data['periode']['mulai'], $data['periode']['selesai']])
            ->select('vehicle_id', DB::raw('COUNT(*) as total_unit'))
            ->groupBy('vehicle_id')
            ->with('vehicle.vehicleModel')
            ->orderByDesc('total_unit')
            ->take(5)
            ->get();

        $topMotors = $bestSelling->map(fn($row) => [
            'name' => $row->vehicle?->vehicleModel?->name ?? 'Unknown',
            'unit' => $row->total_unit,
        ])->toArray();

        $totalIncome = $data['keuangan']['pemasukan'] + $data['penjualan']['total'];

        // Buat prompt AI dengan format paragraf per poin
        $prompt = "Buat 3 insight singkat dan jelas (1 paragraf per poin) untuk laporan Lampegan Motor periode {$data['periode']['mulai']} - {$data['periode']['selesai']}.\n" .
                  "- Pengunjung: {$data['pengunjung']}\n" .
                  "- Penjualan: {$data['penjualan']['jumlah']} unit, total {$data['penjualan']['total']}\n" .
                  "- Pemasukan: {$totalIncome}\n" .
                  "- Pengeluaran: {$data['keuangan']['pengeluaran']}\n" .
                  "- Stok: {$data['stok']}\n" .
                  "- Perpanjangan STNK: {$data['perpanjangan_stnk']}\n" .
                  "- Motor terlaris: " . collect($topMotors)->map(fn($m) => "{$m['name']} → {$m['unit']} unit")->implode(', ');

        $rawInsight = $gemini->generate($prompt);

        // Rapikan: hapus simbol aneh, trim tiap baris
        $insight = preg_replace('/[*]+/', '', $rawInsight);
        $insight = trim($insight);
        // $insight = preg_replace('/^\s+|\s+$/m', '', $insight);
        // $insight = preg_replace('/^\s*\n/m', "\n", $insight);

        return WeeklyReport::create([
            'start_date' => $data['periode']['mulai'],
            'end_date' => $data['periode']['selesai'],
            'visitors' => $data['pengunjung'],
            'sales_count' => $data['penjualan']['jumlah'],
            'sales_total' => $data['penjualan']['total'],
            'income_total' => $data['keuangan']['pemasukan'],
            'expense_total' => $data['keuangan']['pengeluaran'],
            'total_income' => $totalIncome,
            'stock' => $data['stok'],
            'stnk_renewal' => $data['perpanjangan_stnk'],
            'top_motors' => $topMotors,
            'insight' => $insight,
            'read' => false,
        ]);
    }
}
