<x-filament-panels::page>
    <div class="flex justify-between items-center mb-4">
        {{-- Tombol Export Excel --}}
        <x-filament::button
            tag="a"
            href="{{ route('inventory.export.excel') }}"
            color="success"
            icon="heroicon-o-arrow-down-tray"
        >
            Export Excel
        </x-filament::button>
    </div>

    {{-- Tabel otomatis dari HasTable --}}
    {{ $this->table }}
</x-filament-panels::page>
