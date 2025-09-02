<x-filament::page>
    <div class="mb-6">
        <form wire:submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end p-4 border rounded-lg">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                <input type="date" wire:model.defer="from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-opacity-50" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                <input type="date" wire:model.defer="until" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-opacity-50" />
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded bg-gray-700 text-white hover:bg-gray-800">
                    Apply Filter
                </button>
            </div>
        </form>
    </div>

    {{-- tabel filament --}}
    {{ $this->table }}
</x-filament::page>
