<x-filament-panels::page>
    {{ $this->table }}

    <hr class="my-6">

    @livewire(\App\Filament\Widgets\PurchaseUnitsTable::class)

    {{-- <hr class="my-6"> --}}

    {{-- @livewire(\App\Filament\Widgets\TunggakanTable::class) --}}

    <hr class="my-6">

    @livewire(\App\Filament\Widgets\AvailableUnitsTable::class)
</x-filament-panels::page>
