<x-filament::page>
    {{-- FILTER FORM --}}
    <form wire:submit.prevent="submit">
        {{ $this->form }}
    </form>

    {{-- GRID STATISTICS --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($this->getStats() as $stat)
            <x-filament::stats.card
                :label="$stat['label']"
                :value="$stat['value']"
                :description="$stat['desc']"
                :color="$stat['color']"
            />
        @endforeach
    </div>
</x-filament::page>
