<div class="bg-gray-100 py-8">
    <div class="container mx-auto max-w-md px-4" id="filter-section">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-dark mb-8 text-center">Cari Motor Mu</h2>
        {{-- Form method GET untuk filter --}}
        {{-- [DIPERBAIKI] Hapus semua prefix responsif (md:, lg:) --}}
        <form action="{{ route('landing.index') }}" method="GET" class="grid grid-cols-2 gap-4 items-end bg-white p-6 rounded-lg shadow-xl">
            <div>
                <label for="brand" class="block text-gray-800 dark:text-white mb-2 font-semibold text-sm">Merek</label>
                <select name="brand" id="brand" class="w-full p-3 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600">
                    <option value="">Semua Merek</option>
                    @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" @selected(request('brand')==$brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="price" class="block text-gray-800 dark:text-white mb-2 font-semibold text-sm">Harga</label>
                <select name="price" id="price" class="w-full p-3 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600">
                    <option value="semua" @selected(request('price')=='semua' )>Semua Harga</option>
                    <option value="0-40000000" @selected(request('price')=='0-40000000' )>&lt; 40 Jt</option>
                    <option value="40000000-80000000" @selected(request('price')=='40000000-80000000' )>40-80 Jt</option>
                    <option value="80000000-999999999" @selected(request('price')=='80000000-999999999' )>&gt; 80 Jt</option>
                </select>
            </div>

            <div>
                <label for="type" class="block text-gray-800 dark:text-white mb-2 font-semibold text-sm">Tipe</label>
                <select name="type" id="type" class="w-full p-3 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600">
                    <option value="">Semua Tipe</option>
                    @foreach ($types as $type)
                    <option value="{{ $type->id }}" @selected(request('type')==$type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="year" class="block text-gray-800 dark:text-white mb-2 font-semibold text-sm">Tahun</label>
                <select name="year" id="year" class="w-full p-3 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600">
                    <option value="">Semua Tahun</option>
                    @foreach ($years as $year)
                    <option value="{{ $year->id }}" @selected(request('year')==$year->id)>{{ $year->year }}</option>
                    @endforeach
                </select>
            </div>

            {{-- [DIPERBAIKI] col-span-2 agar tombol mengambil lebar penuh --}}
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold p-3 rounded-md flex items-center justify-center w-full transition-transform transform hover:scale-105 col-span-2 mt-2">
                Cari
            </button>
        </form>
    </div>
</div>