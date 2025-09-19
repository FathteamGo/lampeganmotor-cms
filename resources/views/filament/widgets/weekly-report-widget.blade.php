<x-filament::widget wire:poll.10s="checkWeeklyReport">
    {{-- Debug UI --}}
    <div style="background:#f0f0f0;padding:5px;margin-bottom:10px;">
        <strong>Debug Info:</strong><br>
        Polling count: {{ $pollCount }}<br>
        Report exists: {{ $report ? 'Yes' : 'No' }}<br>
        Show modal: {{ $showModal ? 'true' : 'false' }}
    </div>

    @if($report)
        <x-filament::modal :open="$showModal" wire:model="showModal" max-width="lg">
            <x-slot name="title">
                📊 Laporan Mingguan {{ $report->start_date }} - {{ $report->end_date }}
            </x-slot>

            <x-slot name="content">
                <ul class="space-y-1">
                    <li>1. Pengunjung: {{ $report->visitors }}</li>
                    <li>2. Penjualan: {{ $report->sales_count }} unit (Rp {{ number_format($report->sales_total, 0, ',', '.') }})</li>
                    <li>3. Pemasukan: Rp {{ number_format($report->income_total, 0, ',', '.') }}</li>
                    <li>4. Pengeluaran: Rp {{ number_format($report->expense_total, 0, ',', '.') }}</li>
                    <li>5. Stok tersedia: {{ $report->stock }}</li>
                    <li>6. Perpanjangan STNK: {{ $report->stnk_renewal }}</li>
                </ul>

                <h4 class="font-semibold mt-2">🏆 Motor Terlaris</h4>
                <ul class="list-disc list-inside">
                    @foreach($report->top_motors as $motor)
                        <li>{{ $motor['name'] }} → {{ $motor['unit'] }} unit</li>
                    @endforeach
                </ul>

                <h4 class="font-semibold mt-2">💡 Insight</h4>
                <p>{{ $report->insight }}</p>
            </x-slot>

            <x-slot name="footer">
                <x-filament::button color="primary" wire:click="markAsRead">
                    Sudah Dibaca
                </x-filament::button>
            </x-slot>
        </x-filament::modal>
    @endif

    {{-- Debug console --}}
    <script>
        window.addEventListener('weekly-report-debug', event => {
            console.log('WeeklyReportWidget debug:', event.detail);
        });
    </script>
</x-filament::widget>
