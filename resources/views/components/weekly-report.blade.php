<div class="space-y-2">
    <p>Periode: {{ $report->start_date }} - {{ $report->end_date }}</p>
    <p>Pengunjung: {{ $report->visitors }}</p>
    <p>Penjualan: {{ $report->sales_count }} unit (Rp {{ number_format($report->sales_total, 0, ',', '.') }})</p>
    <p>Pemasukan: Rp {{ number_format($report->income_total, 0, ',', '.') }}</p>
    <p>Pengeluaran: Rp {{ number_format($report->expense_total, 0, ',', '.') }}</p>
    <p>Stok tersedia: {{ $report->stock }}</p>
    <p>Perpanjangan STNK: {{ $report->stnk_renewal }}</p>

    <h4 class="font-semibold mt-2">🏆 Motor Terlaris</h4>
    <ul class="list-disc list-inside">
        @foreach($report->top_motors as $motor)
            <li>{{ $motor['name'] }} → {{ $motor['unit'] }} unit</li>
        @endforeach
    </ul>

    <h4 class="font-semibold mt-2">💡 Insight</h4>
    <p>{{ $report->insight }}</p>

    <div class="flex justify-end gap-2 mt-4">
        {{-- Tombol Oke --}}
        <x-filament::button type="button" color="primary" onclick="Livewire.emit('closeModal')">
            Oke
        </x-filament::button>

        {{-- Tombol Jangan Tampil Lagi --}}
        <form method="POST" action="{{ route('weekly-report.dismiss', $report->id) }}">
            @csrf
            <x-filament::button type="submit" color="secondary">
                Faham, jangan tampilkan lagi
            </x-filament::button>
        </form>
    </div>
</div>
