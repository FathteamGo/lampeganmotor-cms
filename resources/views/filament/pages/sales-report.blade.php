<x-filament-panels::page>
    <form wire:submit.prevent="applyFilters">
        <div class="flex items-end gap-2">
            {{-- Input filter --}}
            <div class="flex gap-2">
                {{ $this->form }}
            </div>

            {{-- Tombol sejajar --}}
            <x-filament::button wire:click="exportExcel" type="button" color="success">
                Export Excel
            </x-filament::button>

            <x-filament::button type="submit" color="warning">
                Apply Filter
            </x-filament::button>
        </div>
    </form>

    <div class="mt-6">
        {{ $this->table }}
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
