<x-filament-panels::page>
    <div class="flex justify-between items-center mb-4">
        <x-filament::button
            tag="a"
            href="{{ route('export.asset-report') }}"
            color="success"
            icon="heroicon-o-arrow-down-tray"
        >
            Export Excel
        </x-filament::button>
    </div>

    {{ $this->table }}

    <hr class="my-6">

    @livewire(\App\Filament\Widgets\PurchaseUnitsTable::class)
    <hr class="my-6">

    @livewire(\App\Filament\Widgets\TunggakanTable::class)
    <hr class="my-6">

    @livewire(\App\Filament\Widgets\AvailableUnitsTable::class)
    <hr class="my-6">

    @livewire(\App\Filament\Widgets\AssetTable::class)
</x-filament-panels::page>
