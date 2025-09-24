@php
    $latestReport = \App\Models\WeeklyReport::where('read', 0)->latest('end_date')->first();
    $lastWeek = \App\Models\WeeklyReport::where('end_date', '<', $latestReport?->start_date)
                ->latest('end_date')
                ->first();

    $comparison = "📊 Belum ada data minggu lalu untuk perbandingan.";
    if ($latestReport && $lastWeek) {
        $salesDiff = $latestReport->sales_count - $lastWeek->sales_count;
        $salesPercent = $lastWeek->sales_count > 0
            ? round(($salesDiff / $lastWeek->sales_count) * 100, 1)
            : 0;

        $incomeDiff = $latestReport->total_income - $lastWeek->total_income;
        $incomePercent = $lastWeek->total_income > 0
            ? round(($incomeDiff / $lastWeek->total_income) * 100, 1)
            : 0;

        $comparison =
            "📊 Perbandingan dengan minggu lalu:\n" .
            "• Penjualan: {$latestReport->sales_count} unit (" .
            ($salesDiff >= 0 ? "naik" : "turun") . " {$salesPercent}%)\n" .
            "• Pemasukan: Rp " . number_format($latestReport->total_income, 0, ',', '.') .
            " (" . ($incomeDiff >= 0 ? "naik" : "turun") . " {$incomePercent}%)";
    }

    $topMotors = collect($latestReport?->top_motors)
        ->map(fn($m) => "• {$m['name']} → {$m['unit']} unit")
        ->implode("\n") ?: "Belum ada penjualan minggu ini";
@endphp

<div class="space-y-4 p-4">
    @if($latestReport)
        <p>Ada laporan baru dengan status <b>belum dibaca</b>. Silakan cek detailnya di bawah ini:</p>

        <div class="text-sm text-gray-700 space-y-1">
            <p>Periode: <b>{{ $latestReport->start_date }} - {{ $latestReport->end_date }}</b></p>
            <p>Pengunjung: {{ $latestReport->visitors }}</p>
            <p>Penjualan: {{ $latestReport->sales_count }} unit (Rp {{ number_format($latestReport->sales_total,0,',','.') }})</p>
            <p>Pemasukan: Rp {{ number_format($latestReport->total_income,0,',','.') }}</p>
            <p>Pengeluaran: Rp {{ number_format($latestReport->expense_total,0,',','.') }}</p>
            <p>Stok tersedia: {{ $latestReport->stock }}</p>
            <p>Perpanjangan STNK: {{ $latestReport->stnk_renewal }}</p>
            <p>🏆 Motor Terlaris:<br>{{ nl2br(e($topMotors)) }}</p>
            <p>{{ nl2br(e($comparison)) }}</p>
        </div>
    @else
        <p class="text-sm text-gray-500">Tidak ada laporan baru.</p>
    @endif
</div>
