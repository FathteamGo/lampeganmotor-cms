<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\Sale;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Vehicle;
use App\Models\StnkRenewal;
use App\Models\WeeklyReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function generateWeeklyReport(): array
    {
        $today = now();

        // Default: Senin minggu ini -> sekarang
        $start = $today->copy()->startOfWeek(Carbon::MONDAY);
        $end   = $today->copy()->endOfDay();

        // Kalau Minggu subuh (scheduler jalan)
        if ($today->isSunday() && $today->hour < 6) {
            $start = $today->copy()->subWeek()->startOfWeek(Carbon::MONDAY);
            $end   = $today->copy()->subDay()->endOfDay(); // Sabtu
        }

        $pengunjung = Visitor::whereBetween('visited_at', [$start, $end])->count();
        $penjualanJumlah = Sale::whereBetween('sale_date', [$start, $end])->count();
        $penjualanTotal  = Sale::whereBetween('sale_date', [$start, $end])->sum('sale_price');

        $pemasukan   = Income::whereBetween('income_date', [$start, $end])->sum('amount');
        $pengeluaran = Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
        $saldo       = $pemasukan - $pengeluaran;

        $stok = Vehicle::doesntHave('sale')->count();
        $stnk = StnkRenewal::whereBetween('tgl', [$start, $end])->count();

        return [
            'periode' => [
                'mulai'   => $start->toDateString(),
                'selesai' => $end->toDateString(),
            ],
            'pengunjung' => $pengunjung,
            'penjualan' => [
                'jumlah' => $penjualanJumlah,
                'total'  => $penjualanTotal,
            ],
            'keuangan' => [
                'pemasukan'   => $pemasukan,
                'pengeluaran' => $pengeluaran,
                'saldo'       => $saldo,
            ],
            'stok' => $stok,
            'perpanjangan_stnk' => $stnk,
        ];
    }

    public function saveWeeklyReport(\App\Services\GeminiService $gemini): WeeklyReport
    {
        $data = $this->generateWeeklyReport();

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

        $lastWeek = WeeklyReport::orderByDesc('end_date')->first();
        $comparison = [];

        if ($lastWeek) {
            $comparison['sales'] = $this->compareValue($data['penjualan']['total'], $lastWeek->sales_total, 'Penjualan');
            $comparison['visitors'] = $this->compareValue($data['pengunjung'], $lastWeek->visitors, 'Pengunjung');
            $comparison['income'] = $this->compareValue($totalIncome, $lastWeek->total_income, 'Pemasukan');
        }

        $prompt = "Buat 3 insight singkat untuk laporan Lampegan Motor periode {$data['periode']['mulai']} - {$data['periode']['selesai']}.\n" .
                  "- Pengunjung: {$data['pengunjung']}\n" .
                  "- Penjualan: {$data['penjualan']['jumlah']} unit, total {$data['penjualan']['total']}\n" .
                  "- Pemasukan: {$totalIncome}\n" .
                  "- Pengeluaran: {$data['keuangan']['pengeluaran']}\n" .
                  "- Stok: {$data['stok']}\n" .
                  "- Perpanjangan STNK: {$data['perpanjangan_stnk']}\n" .
                  "- Motor terlaris: " . collect($topMotors)->map(fn($m) => "{$m['name']} → {$m['unit']} unit")->implode(', ') . "\n" .
                  "- Perbandingan Minggu Lalu: " . implode(', ', $comparison);

        $rawInsight = $gemini->generate($prompt);
        $insight = trim(preg_replace('/[*]+/', '', $rawInsight));

        return WeeklyReport::create([
            'start_date'    => $data['periode']['mulai'],
            'end_date'      => $data['periode']['selesai'],
            'visitors'      => $data['pengunjung'],
            'sales_count'   => $data['penjualan']['jumlah'],
            'sales_total'   => $data['penjualan']['total'],
            'income_total'  => $data['keuangan']['pemasukan'],
            'expense_total' => $data['keuangan']['pengeluaran'],
            'total_income'  => $totalIncome,
            'stock'         => $data['stok'],
            'stnk_renewal'  => $data['perpanjangan_stnk'],
            'top_motors'    => $topMotors,
            'insight'       => $insight,
            'read'          => false,
        ]);
    }

    private function compareValue($current, $previous, $label): string
    {
        if ($previous == 0) {
            return "$label minggu lalu 0, tidak bisa dibandingkan";
        }

        $diff = $current - $previous;
        $percent = round(($diff / $previous) * 100, 1);

        if ($diff > 0) {
            return "$label naik {$percent}% dibanding minggu lalu";
        } elseif ($diff < 0) {
            return "$label turun " . abs($percent) . "% dibanding minggu lalu";
        }
        return "$label stabil dibanding minggu lalu";
    }
}
