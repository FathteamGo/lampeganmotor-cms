<x-filament::page>
    <x-filament::button wire:click="exportExcel">
        Export Excel
    </x-filament::button>
    {{ $this->table }}  
</x-filament::page>
