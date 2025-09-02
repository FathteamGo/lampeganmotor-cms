<x-filament-panels::page>
    {{-- Header + Summary kecil --}}
    <div class="flex flex-col gap-3 mb-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold">Laporan & Audit Profit & Loss</h2>

            <div class="flex items-center gap-2">
                <x-filament::button icon="heroicon-o-arrow-down-tray" wire:click="$refresh">
                    Export Excel
                </x-filament::button>
                <x-filament::button icon="heroicon-o-share">
                    Generate to WhatsApp
                </x-filament::button>
            </div>
        </div>
    </div>

    {{-- Body: Sales (full), Income & Expense (2 kolom), Summary besar --}}
    <div class="grid grid-cols-12 gap-4">
        {{-- SALES: filter tanggal ADA di dalam widget ini --}}
        <div class="col-span-12">
            @livewire(\App\Filament\Widgets\SalesTable::class,
                ['dateStart' => $dateStart, 'dateEnd' => $dateEnd],
                key('sales-'.$dateStart.$dateEnd))
        </div>

        {{-- INCOME (kiri) --}}
        <div class="col-span-12 lg:col-span-6">
            @livewire(\App\Filament\Widgets\IncomeTable::class,
                ['dateStart' => $dateStart, 'dateEnd' => $dateEnd],
                key('income-'.$dateStart.$dateEnd))
        </div>

        {{-- EXPENSE (kanan) --}}
        <div class="col-span-12 lg:col-span-6">
            @livewire(\App\Filament\Widgets\ExpenseTable::class,
                ['dateStart' => $dateStart, 'dateEnd' => $dateEnd],
                key('expense-'.$dateStart.$dateEnd))
        </div>

{{-- SUMMARY BOX --}}
<div class="col-span-12">
    <div class="rounded-xl border border-gray-200 dark:border-gray-800
                bg-white dark:bg-gray-900/50
                shadow-sm overflow-hidden">   {{-- ⬅️ tambah shadow + bg + overflow-hidden --}}
        <table class="min-w-full text-xs">
            <tbody>
            <tr class="border-b dark:border-gray-800">
                <td class="px-3 py-2 w-32">SALES</td>
                <td class="px-3 py-2 text-right">{{ $this->formatIdr($totalSales) }}</td>
            </tr>
            <tr class="border-b dark:border-gray-800">
                <td class="px-3 py-2">INCOME</td>
                <td class="px-3 py-2 text-right">{{ $this->formatIdr($totalIncomes) }}</td>
            </tr>
            <tr class="border-b dark:border-gray-800">
                <td class="px-3 py-2">EXPENSE</td>
                <td class="px-3 py-2 text-right">{{ $this->formatIdr($totalExpenses) }}</td>
            </tr>
            @php $profit = $this->profit; @endphp
            <tr>
                <td class="px-3 py-2 font-semibold">Total</td>
                <td class="px-3 py-2 text-right font-semibold">
                    {{ $profit >= 0 ? '(' . $this->formatIdr($profit) . ') Profit' : '(' . $this->formatIdr(abs($profit)) . ') Loss' }}
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>


    </div>
</x-filament-panels::page>
