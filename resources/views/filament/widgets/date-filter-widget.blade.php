<x-filament::widget>
    <x-filament::card>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Start Date --}}
            <div>
                <x-filament::input
                    wire:model.live="startDate"
                    type="date"
                    label="Tanggal Mulai"
                />
            </div>

            {{-- End Date --}}
            <div>
                <x-filament::input
                    wire:model.live="endDate"
                    type="date"
                    label="Tanggal Selesai"
                />
            </div>

            {{-- Reset Button --}}
            <div class="flex items-end">
                <x-filament::button color="secondary" wire:click="resetFilter">
                    Reset
                </x-filament::button>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
