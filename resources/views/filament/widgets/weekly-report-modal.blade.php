<div
    x-data="{ open: @entangle('open'), title: @entangle('title'), date: @entangle('date'), content: @entangle('content') }"
    wire:key="weekly-report-modal"
>
    <x-filament::modal x-show="open" x-cloak>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span x-text="title"></span>
            </div>
        </x-slot>

        <div class="mt-2">
            <p class="text-sm text-gray-500 mb-2" x-text="date"></p>
            <pre class="text-gray-700 whitespace-pre-wrap" x-text="content"></pre>
        </div>

        <div class="flex justify-end space-x-2 mt-4">
            <x-filament::button color="gray" @click="open = false">Close</x-filament::button>
            <x-filament::button color="primary" @click="open = false">Oke</x-filament::button>
            <x-filament::button color="success" @click="open = false">Sudah Paham</x-filament::button>
        </div>
    </x-filament::modal>
</div>