<x-filament-panels::page>
    <h2 class="text-lg font-bold mb-2">Laporan Asset Lain</h2>
    {{ $this->table }}

    <hr class="my-6">

    @livewire(\App\Filament\Widgets\AvailableUnitsTable::class)
</x-filament-panels::page>

