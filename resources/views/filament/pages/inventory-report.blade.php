<x-filament-panels::page>
    <div class="flex justify-between items-center mb-6">
        <input type="text" placeholder="🔍 Search" class="border border-gray-300 rounded-lg px-4 py-2 w-1/3 focus:ring-2 focus:ring-green-500 focus:outline-none">
        <button class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg shadow">
            Export Excel
        </button>
    </div>

    <div class="space-y-6">
        @foreach ($this->vehicles as $vehicle)
        <div class="bg-white rounded-xl shadow-md border p-5">
            {{-- Header / Judul Kendaraan --}}
            <h2 class="font-bold text-lg text-gray-800 mb-4">
                #{{ $vehicle->id }} - {{ optional($vehicle->vehicleModel)->name }}
            </h2>

            {{-- Konten --}}
            <div class="flex gap-5">
                {{-- Foto --}}
                <div class="w-32 h-24 bg-gray-100 flex items-center justify-center rounded overflow-hidden">
                    @if ($vehicle->photos->first())
                    <img src="{{ asset('storage/' . $vehicle->photos->first()->path) }}" class="object-cover w-full h-full">
                    @else
                    <span class="text-gray-400 text-sm">No Image</span>
                    @endif
                </div>

                {{-- Detail --}}
                <div class="flex-1 grid grid-cols-2 gap-y-2 text-sm text-gray-700">
                    <div>
                        <p><span class="font-semibold">Brand:</span> {{ optional($vehicle->vehicleModel->brand)->name }}</p>
                        <p><span class="font-semibold">Type:</span> {{ optional($vehicle->type)->name }}</p>
                        <p><span class="font-semibold">Model:</span> {{ optional($vehicle->vehicleModel)->name }}</p>
                        <p><span class="font-semibold">Years:</span> {{ optional($vehicle->year)->year }}</p>
                        <p><span class="font-semibold">License Plate:</span> {{ $vehicle->license_plate }}</p>
                    </div>
                    <div>
                        <p><span class="font-semibold">Purchase Price:</span> Rp {{ number_format($vehicle->purchase_price, 0, ',', '.') }}</p>
                        <p><span class="font-semibold">HTP:</span> Rp {{ number_format($vehicle->sale_price, 0, ',', '.') }}</p>
                        <p><span class="font-semibold">Tax:</span> Rp </p>
                        <p><span class="font-semibold">Pale Perawatan:</span> Rp </p>
                        <p><span class="font-semibold">commssion:</span> Rp </p>
                        <p><span class="font-semibold">Shipping cost:</span> Rp </p>


                        {{-- Additional Costs --}}
                        @foreach ($vehicle->additionalCosts as $cost)
                        <p><span class="font-semibold">{{ $cost->name }}:</span> Rp {{ number_format($cost->amount, 0, ',', '.') }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</x-filament-panels::page>

