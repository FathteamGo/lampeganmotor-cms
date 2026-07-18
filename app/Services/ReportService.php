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

        // Exclude cancel
        $penjualanJumlah = Sale::whereBetween('sale_date', [$start, $end])
            ->where('status', '!=', 'cancel')
            ->count();

        $penjualanTotal  = Sale::whereBetween('sale_date', [$start, $end])
            ->where('status', '!=', 'cancel')
            ->sum('sale_price');

        $pemasukan   = Income::whereBetween('income_date', [$start, $end])->sum('amount');
        $pengeluaran = Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
        $saldo       = $pemasukan - $pengeluaran;

        $stok = Vehicle::where('status', 'available')->count();
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

    public function saveWeeklyReport(\App\Services\AiServiceInterface $gemini): WeeklyReport
    {
        $data = $this->generateWeeklyReport();

        // Exclude cancel, group by brand & model
        $bestSelling = Sale::join('vehicles', 'sales.vehicle_id', '=', 'vehicles.id')
            ->join('vehicle_models', 'vehicles.vehicle_model_id', '=', 'vehicle_models.id')
            ->leftJoin('brands', 'vehicle_models.brand_id', '=', 'brands.id')
            ->whereBetween('sales.sale_date', [$data['periode']['mulai'], $data['periode']['selesai']])
            ->where('sales.status', '!=', 'cancel')
            ->select(
                'vehicle_models.name as model_name',
                'brands.name as brand_name',
                DB::raw('COUNT(sales.id) as total_unit')
            )
            ->groupBy('vehicle_models.id', 'vehicle_models.name', 'brands.name')
            ->orderByDesc('total_unit')
            ->take(5)
            ->get();

        $topMotors = $bestSelling->map(function ($row) {
            $brand = trim($row->brand_name);
            $model = trim($row->model_name);
            $name = $brand && !str_starts_with(strtolower($model), strtolower($brand))
                ? "{$brand} {$model}"
                : $model;
            
            return [
                'name' => $name ?: 'Unknown',
                'unit' => $row->total_unit,
            ];
        })->toArray();

        $totalIncome = $data['keuangan']['pemasukan'] + $data['penjualan']['total'];

        $lastWeek = WeeklyReport::orderByDesc('end_date')->first();
        $comparison = [];

        if ($lastWeek) {
            $comparison['sales']    = $this->compareValue($data['penjualan']['total'], $lastWeek->sales_total, 'Penjualan');
            $comparison['visitors'] = $this->compareValue($data['pengunjung'], $lastWeek->visitors, 'Pengunjung');
            $comparison['income']   = $this->compareValue($totalIncome, $lastWeek->total_income, 'Pemasukan');
        }

        $bosName = env('BOS_NAME', 'Bos');
        $showroomName = env('SHOWROOM_NAME', 'Lampegan Motor');

        $prompt = "Sebagai Hana AI, asisten AI wanita Muslim yang sangat ramah, santun, dan profesional untuk {$showroomName}, buatlah 3 insight bisnis singkat, strategis, dan sopan untuk {$bosName} (panggilan untuk owner) mengenai laporan mingguan periode {$data['periode']['mulai']} - {$data['periode']['selesai']}.\n" .
                  "Gunakan bahasa Indonesia yang santun. Awali dengan salam hangat Islami yang sopan (seperti Assalamu'alaikum Warahmatullahi Wabarakatuh, semoga {$bosName} selalu sehat wal afiat dan usahanya berkah, dll.), panggil beliau dengan sebutan '{$bosName}' secara konsisten, dan berikan analisis performa bisnis yang bermanfaat.\n" .
                  "PENTING: Format jawaban HARUS menggunakan format WhatsApp (*tebal*, _miring_). DILARANG menggunakan format Markdown Header seperti '####', '#', atau '**'. Pisahkan paragraf dengan enter yang rapi.\n" .
                  "SANGAT PENTING: JANGAN menulis ulang atau merangkum angka-angka data mentah (seperti total pengunjung, total penjualan, pemasukan, dll) di dalam jawabanmu. Angka-angka tersebut sudah otomatis dilampirkan oleh sistem di bawah pesanmu. Langsung saja fokus berikan 3 insight dan evaluasinya.\n" .
                  "DILARANG KERAS: Jangan tampilkan proses berpikirmu, draf, atau instruksi internal (seperti 'We need to...', 'Let's craft:', dsb). LANGSUNG berikan hasil akhir dalam bahasa Indonesia.\n" .
                  "Data Laporan:\n" .
                  "- Pengunjung: {$data['pengunjung']} orang\n" .
                  "- Penjualan: {$data['penjualan']['jumlah']} unit, total Rp " . number_format($data['penjualan']['total'], 0, ',', '.') . "\n" .
                  "- Total Pemasukan: Rp " . number_format($totalIncome, 0, ',', '.') . "\n" .
                  "- Pengeluaran: Rp " . number_format($data['keuangan']['pengeluaran'], 0, ',', '.') . "\n" .
                  "- Stok Showroom: {$data['stok']} unit\n" .
                  "- Perpanjangan STNK: {$data['perpanjangan_stnk']} transaksi\n" .
                  "- Motor terlaris: " . collect($topMotors)->map(fn($m) => "{$m['name']} → {$m['unit']} unit")->implode(', ') . "\n" .
                  "- Perbandingan Minggu Lalu: " . implode(', ', $comparison) . "\n\n" .
                  "ATURAN MUTLAK:\n" .
                  "1. Kata pertama dari balasanmu HARUS 'Assalamu'alaikum'.\n" .
                  "2. DILARANG keras menyertakan proses berpikir, draf, terjemahan instruksi (seperti 'We need to...', 'Let's craft'), atau teks bahasa Inggris apa pun.\n" .
                  "3. HANYA keluarkan teks hasil akhir dalam bahasa Indonesia yang rapi.";

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

    /**
     * Generate analytics data for the past 30 days.
     */
    public function generate30DayReportData(): array
    {
        $today = now();
        $start = $today->copy()->subDays(30)->startOfDay();
        $end   = $today->copy()->endOfDay();

        $pengunjung = Visitor::whereBetween('visited_at', [$start, $end])->count();

        // Exclude cancel
        $penjualanJumlah = Sale::whereBetween('sale_date', [$start, $end])
            ->where('status', '!=', 'cancel')
            ->count();

        $penjualanTotal  = Sale::whereBetween('sale_date', [$start, $end])
            ->where('status', '!=', 'cancel')
            ->sum('sale_price');

        $pemasukan   = Income::whereBetween('income_date', [$start, $end])->sum('amount');
        $pengeluaran = Expense::whereBetween('expense_date', [$start, $end])->sum('amount');
        $saldo       = $pemasukan - $pengeluaran;

        $stok = Vehicle::where('status', 'available')->count();
        $stnk = StnkRenewal::whereBetween('tgl', [$start, $end])->count();

        // Top Selling in past 30 days grouped by brand & model
        $bestSelling = Sale::join('vehicles', 'sales.vehicle_id', '=', 'vehicles.id')
            ->join('vehicle_models', 'vehicles.vehicle_model_id', '=', 'vehicle_models.id')
            ->leftJoin('brands', 'vehicle_models.brand_id', '=', 'brands.id')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->where('sales.status', '!=', 'cancel')
            ->select(
                'vehicle_models.name as model_name',
                'brands.name as brand_name',
                DB::raw('COUNT(sales.id) as total_unit')
            )
            ->groupBy('vehicle_models.id', 'vehicle_models.name', 'brands.name')
            ->orderByDesc('total_unit')
            ->take(5)
            ->get();

        $topMotors = $bestSelling->map(function ($row) {
            $brand = trim($row->brand_name);
            $model = trim($row->model_name);
            $name = $brand && !str_starts_with(strtolower($model), strtolower($brand))
                ? "{$brand} {$model}"
                : $model;
            
            return [
                'name' => $name ?: 'Unknown',
                'unit' => $row->total_unit,
            ];
        })->toArray();

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
            'top_motors' => $topMotors,
            'total_income' => $penjualanTotal + $pemasukan,
        ];
    }

    /**
     * Generate AI business insight for 30-day report.
     */
    public function generate30DayInsight(array $data, AiServiceInterface $ai): string
    {
        $topMotors = collect($data['top_motors'])
            ->map(fn($m) => "{$m['name']} → {$m['unit']} unit")
            ->implode(', ') ?: 'Tidak ada penjualan';

        $bosName = env('BOS_NAME', 'Bos');
        $showroomName = env('SHOWROOM_NAME', 'Lampegan Motor');

        $prompt = "Sebagai Hana AI, asisten AI wanita Muslim yang sangat ramah, santun, dan profesional untuk showroom {$showroomName}, buatlah analisis bisnis strategis dan 3 insight bisnis penting jangka menengah untuk {$bosName} (panggilan untuk owner) mengenai perkembangan bisnis selama 30 hari ke belakang (periode {$data['periode']['mulai']} - {$data['periode']['selesai']}).\n" .
                  "Gunakan bahasa Indonesia yang sangat sopan dan penuh rasa hormat. Awali dengan salam hangat Islami yang sopan (seperti Assalamu'alaikum Warahmatullahi Wabarakatuh, semoga {$bosName} selalu sehat wal afiat dan usahanya berkah, dll.), sebut beliau dengan panggilan '{$bosName}' secara konsisten, dan berikan masukan strategis jangka menengah untuk meningkatkan penjualan.\n" .
                  "PENTING: Format jawaban HARUS menggunakan format WhatsApp (*tebal*, _miring_). DILARANG menggunakan format Markdown Header seperti '####', '#', atau '**'. Gunakan penomoran angka biasa (1. , 2. ) dan pisahkan antar poin dengan enter (baris baru) yang rapi.\n" .
                  "SANGAT PENTING: JANGAN menulis ulang atau merangkum angka-angka data mentah (seperti total pengunjung, total penjualan, pemasukan, dll) di dalam jawabanmu. Angka-angka tersebut sudah otomatis dilampirkan oleh sistem di bawah pesanmu. Langsung saja fokus berikan 3 insight dan evaluasinya.\n" .
                  "DILARANG KERAS: Jangan tampilkan proses berpikirmu, draf, atau instruksi internal (seperti 'We need to...', 'Let's craft:', dsb). LANGSUNG berikan hasil akhir dalam bahasa Indonesia.\n" .
                  "Data 30 hari ke belakang:\n" .
                  "- Pengunjung: {$data['pengunjung']} orang\n" .
                  "- Penjualan: {$data['penjualan']['jumlah']} unit (total Rp " . number_format($data['penjualan']['total'], 0, ',', '.') . ")\n" .
                  "- Pemasukan Tambahan: Rp " . number_format($data['keuangan']['pemasukan'], 0, ',', '.') . "\n" .
                  "- Total Pemasukan: Rp " . number_format($data['total_income'], 0, ',', '.') . "\n" .
                  "- Pengeluaran Operasional: Rp " . number_format($data['keuangan']['pengeluaran'], 0, ',', '.') . "\n" .
                  "- Sisa Stok Showroom: {$data['stok']} unit\n" .
                  "- Perpanjangan STNK: {$data['perpanjangan_stnk']} transaksi\n" .
                  "- Unit Terlaris: {$topMotors}\n\n" .
                  "ATURAN MUTLAK:\n" .
                  "1. Kata pertama dari balasanmu HARUS 'Assalamu'alaikum'.\n" .
                  "2. DILARANG keras menyertakan proses berpikir, draf, terjemahan instruksi (seperti 'We need to...', 'Let's craft'), atau teks bahasa Inggris apa pun.\n" .
                  "3. HANYA keluarkan teks hasil akhir dalam bahasa Indonesia yang rapi.";

        $rawInsight = $ai->generate($prompt);
        return trim(preg_replace('/[*]+/', '', $rawInsight));
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
