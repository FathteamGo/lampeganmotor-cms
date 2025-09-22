<div>
    @if($show && $report)
        <x-filament::modal id="weekly-report-modal" :show="$show">
            <h2 class="text-lg font-bold mb-2">Weekly Report Terbaru</h2>

            <div class="space-y-1 mb-4">
                <p>Periode: {{ $report->start_date }} - {{ $report->end_date }}</p>
                <p>Pengunjung: {{ $report->visitors }}</p>
                <p>Penjualan: {{ $report->sales_count }} unit, total Rp {{ number_format($report->sales_total,0,',','.') }}</p>
                <p>Pemasukan: Rp {{ number_format($report->income_total,0,',','.') }}</p>
                <p>Pengeluaran: Rp {{ number_format($report->expense_total,0,',','.') }}</p>
                <p>Stok: {{ $report->stock }}</p>
                <p>Perpanjangan STNK: {{ $report->stnk_renewal }}</p>
                <p>Motor Terlaris: {{ collect($report->top_motors)->map(fn($m) => "{$m['name']} → {$m['unit']} unit")->implode(', ') }}</p>
                <p>Insight: {{ $report->insight }}</p>
            </div>

            <div class="flex justify-end gap-2">
                <x-filament::button wire:click="oke">Oke</x-filament::button>
                <x-filament::button wire:click="janganTampilLagi" color="secondary">Faham, jangan tampilkan lagi</x-filament::button>
            </div>
        </x-filament::modal>
    @endif
</div>
