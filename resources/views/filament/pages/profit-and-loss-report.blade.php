<x-filament-panels::page>
    {{-- TOOLBAR ATAS --}}
    <div class="p-4 rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
        <x-filament::section class="mt-2 shadow-sm">
            <div class="fi-section-content p-0">
                <div class="flex items-center gap-3 md:gap-4 flex-nowrap">
                    {{-- SEARCH kiri --}}
                    <div class="w-72 md:w-80">
                        <x-filament::input.wrapper>
                            <x-slot name="prefix">
                                <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-5 w-5 text-gray-400" />
                            </x-slot>
                            <x-filament::input
                                wire:model.live.debounce.400ms="search"
                                placeholder="Cari…"
                                autocomplete="off"
                            />
                        </x-filament::input.wrapper>
                    </div>
                    <br>
                    {{-- TOMBOL kanan --}}
                    <div class="ml-auto flex items-center gap-2 whitespace-nowrap shrink-0">
                        <x-filament::button color="success" icon="heroicon-o-arrow-down-tray" wire:click="exportToExcel">
                            Export Excel
                        </x-filament::button>
                        <x-filament::button icon="heroicon-o-paper-airplane" wire:click="sendWhatsAppAuto">
                            Generate to WhatsApp
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>

    {{-- KONTEN --}}
    <div class="space-y-6 mt-6">
        {{-- SALES --}}
        <div class="p-4 rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
            @livewire(\App\Filament\Widgets\SalesTable::class,
                ['dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'search' => $search],
                key('sales-'.$dateStart.$dateEnd.'-'.$search))
        </div>

        <br>

        {{-- INCOME --}}
        <div class="p-4 rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
            @livewire(\App\Filament\Widgets\IncomeTable::class,
                ['dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'search' => $search],
                key('income-'.$dateStart.$dateEnd.'-'.$search))
        </div>

        <br>

        {{-- EXPENSE --}}
        <div class="p-4 rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
            @livewire(\App\Filament\Widgets\ExpenseTable::class,
                ['dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'search' => $search],
                key('expense-'.$dateStart.$dateEnd.'-'.$search))
        </div>

        <br>

        {{-- ASSET: pakai widget summary table --}}
        @livewire(\App\Filament\Widgets\AssetSummaryTable::class, [
            'totalSales'     => $totalSales,
            'totalIncomes'   => $totalIncomes,
            'totalExpenses'  => $totalExpenses,
        ], key('asset-'.$dateStart.$dateEnd))
    </div>
</x-filament-panels::page>
