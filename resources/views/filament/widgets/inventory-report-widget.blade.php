<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($vehicles as $vehicle)
            <div class="p-4 bg-white rounded-lg shadow">
                {{-- Foto --}}
                <img src="{{ $vehicle->photos->first()?->path
                                ? asset('storage/' . $vehicle->photos->first()->path)
                                : asset('images/no-image.png') }}" class="w-full h-40 object-cover rounded mb-2">

                {{-- Detail --}}
                <div class="grid grid-cols-2 gap-x-4 text-sm">
                    <div>
                        <p><b>Merek</b> : {{ $vehicle->vehicleModel->brand->name ?? '-' }}</p>
                        <p><b>Type</b> : {{ $vehicle->type->name ?? '-' }}</p>
                        <p><b>Model</b> : {{ $vehicle->vehicleModel->name ?? '-' }}</p>
                        <p><b>Tahun</b> : {{ $vehicle->year->year ?? '-' }}</p>
                        <p><b>No Polisi</b> : {{ $vehicle->license_plate ?? '-' }}</p>
                    </div>
                    <div>
                        <p><b>Harga Beli</b> : Rp {{ number_format($vehicle->purchase_price, 0, ',', '.') }}</p>
                        <p><b>HTP</b> : Rp {{ number_format($vehicle->sale_price, 0, ',', '.') }}</p>
                        <p><b>Pajak</b> : Rp {{ number_format($vehicle->tax, 0, ',', '.') }}</p>
                        <p><b>Perawatan</b> : Rp {{ number_format($vehicle->maintenance_fee, 0, ',', '.') }}</p>
                        <p><b>Komisi</b> : Rp {{ number_format($vehicle->commission, 0, ',', '.') }}</p>
                        <p><b>Ongkir</b> : Rp {{ number_format($vehicle->delivery_fee, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

