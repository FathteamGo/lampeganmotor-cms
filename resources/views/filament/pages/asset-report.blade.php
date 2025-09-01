<x-filament-panels::page>
    <h1 class="text-2xl font-bold mb-4">Asset Report</h1>

    {{-- Fixed Assets --}}
    <h2 class="text-xl font-semibold mb-2"></h2>
    <table class="table-auto border-collapse border border-gray-400 w-full mb-6">
        <thead>
            <tr class="bg-gray-200">
                <th class="border border-gray-400 px-2 py-1">#</th>
                <th class="border border-gray-400 px-2 py-1">Name</th>
                <th class="border border-gray-400 px-2 py-1">Year</th>
                <th class="border border-gray-400 px-2 py-1">Description</th>
                <th class="border border-gray-400 px-2 py-1">Value</th>
            </tr>
        </thead>
        <tbody>
        @foreach($fixedAssets as $i => $item)
            <tr>
                <td class="border border-gray-400 px-2 py-1">{{ $i+1 }}</td>
                <td class="border border-gray-400 px-2 py-1">{{ $item->name }}</td>
                <td class="border border-gray-400 px-2 py-1">{{ \Carbon\Carbon::parse($item->acquisition_date)->year }}</td>
                <td class="border border-gray-400 px-2 py-1">{{ $item->description }}</td>
                <td class="border border-gray-400 px-2 py-1">Rp {{ number_format($item->value,0,',','.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Moving Assets --}}
    <h2 class="text-xl font-semibold mb-2"></h2>
    <table class="table-auto border-collapse border border-gray-400 w-full mb-6">
        <thead>
            <tr class="bg-gray-200">
                <th class="border border-gray-400 px-2 py-1">#</th>
                <th class="border border-gray-400 px-2 py-1">Name</th>
                <th class="border border-gray-400 px-2 py-1">Year</th>
                <th class="border border-gray-400 px-2 py-1">Description</th>
                <th class="border border-gray-400 px-2 py-1">Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border border-gray-400 px-2 py-1">1</td>
                <td class="border border-gray-400 px-2 py-1">Motor</td>
                <td class="border border-gray-400 px-2 py-1">2024</td>
                <td class="border border-gray-400 px-2 py-1">Motor operasional</td>
                <td class="border border-gray-400 px-2 py-1">Rp 8.000.000</td>
            </tr>
        </tbody>
    </table>

    {{-- Arrears --}}
    <h2 class="text-xl font-semibold mb-2"></h2>
    <table class="table-auto border-collapse border border-gray-400 w-full mb-6">
        <thead>
            <tr class="bg-gray-200">
                <th class="border border-gray-400 px-2 py-1">#</th>
                <th class="border border-gray-400 px-2 py-1">Name</th>
                <th class="border border-gray-400 px-2 py-1">Year</th>
                <th class="border border-gray-400 px-2 py-1">Description</th>
                <th class="border border-gray-400 px-2 py-1">Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border border-gray-400 px-2 py-1">1</td>
                <td class="border border-gray-400 px-2 py-1">Tunggakan</td>
                <td class="border border-gray-400 px-2 py-1">2023</td>
                <td class="border border-gray-400 px-2 py-1">Arrears pembayaran sewa</td>
                <td class="border border-gray-400 px-2 py-1">Rp 5.000.000</td>
            </tr>
        </tbody>
    </table>

    {{-- Non-Moving Assets --}}
    <h2 class="text-xl font-semibold mb-2"></h2>
    <table class="table-auto border-collapse border border-gray-400 w-full mb-6">
        <thead>
            <tr class="bg-gray-200">
                <th class="border border-gray-400 px-2 py-1">#</th>
                <th class="border border-gray-400 px-2 py-1">Name</th>
                <th class="border border-gray-400 px-2 py-1">Year</th>
                <th class="border border-gray-400 px-2 py-1">Description</th>
                <th class="border border-gray-400 px-2 py-1">Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border border-gray-400 px-2 py-1">1</td>
                <td class="border border-gray-400 px-2 py-1">Tanah Kosong</td>
                <td class="border border-gray-400 px-2 py-1">2022</td>
                <td class="border border-gray-400 px-2 py-1">Non-Moving asset (tanah)</td>
                <td class="border border-gray-400 px-2 py-1">Rp 250.000.000</td>
            </tr>
        </tbody>
    </table>

    {{-- Total --}}
    <h2 class="text-lg font-bold">Total Assets: Rp 1.263.000.000</h2>
</x-filament-panels::page>

