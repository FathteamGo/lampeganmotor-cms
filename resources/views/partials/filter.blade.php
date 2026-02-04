<!-- Filter Section -->
<section id="filter-section" class="bg-white py-8">
  <div class="mx-auto w-full max-w-sm md:max-w-7xl px-4">

    {{-- Judul --}}
    <h2 class="text-2xl font-extrabold text-center text-black mb-6 inline-block border-b-4 border-yellow-400 px-2">
      Cari Motor Mu
    </h2>

    {{-- Form filter --}}
    <form action="{{ route('landing.index') }}" method="GET"
          class="grid grid-cols-2 md:grid-cols-5 gap-4 items-end bg-white p-4 rounded-lg shadow-md">

      {{-- Merek --}}
      <div>
        <label for="brand" class="block text-sm font-semibold text-black mb-1">Merek</label>
        <div class="relative">
          <select id="brand" name="brand"
                  class="w-full h-11 p-2 pr-9 rounded-md bg-white text-black
                         border border-black/30 focus:border-red-600 focus:ring-2 focus:ring-red-200
                         appearance-none">
            <option value="">Semua Merek</option>
            @foreach ($brands as $brand)
              <option value="{{ $brand->id }}" @selected(request('brand')==$brand->id)>{{ $brand->name }}</option>
            @endforeach
          </select>
          {{-- Chevron --}}
          <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-5 w-5 text-black/60"
               fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </div>
      </div>

      {{-- Harga --}}
      <div>
        <label for="price" class="block text-sm font-semibold text-black mb-1">Harga</label>
        <div class="relative">
          <select id="price" name="price"
                  class="w-full h-11 p-2 pr-9 rounded-md bg-white text-black
                         border border-black/30 focus:border-red-600 focus:ring-2 focus:ring-red-200
                         appearance-none">
            <option value="semua" @selected(request('price')=='semua')>Semua Harga</option>
            <option value="0-40000000" @selected(request('price')=='0-40000000')>&lt; 40 Jt</option>
            <option value="40000000-80000000" @selected(request('price')=='40000000-80000000')>40–80 Jt</option>
            <option value="80000000-999999999" @selected(request('price')=='80000000-999999999')>&gt; 80 Jt</option>
          </select>
          <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-5 w-5 text-black/60"
               fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </div>
      </div>

      {{-- Tipe --}}
      <div>
        <label for="type" class="block text-sm font-semibold text-black mb-1">Tipe</label>
        <div class="relative">
          <select id="type" name="type"
                  class="w-full h-11 p-2 pr-9 rounded-md bg-white text-black
                         border border-black/30 focus:border-red-600 focus:ring-2 focus:ring-red-200
                         appearance-none">
            <option value="">Semua Tipe</option>
            @foreach ($types as $type)
              <option value="{{ $type->id }}" @selected(request('type')==$type->id)>{{ $type->name }}</option>
            @endforeach
          </select>
          <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-5 w-5 text-black/60"
               fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </div>
      </div>

      {{-- Tahun --}}
      <div>
        <label for="year" class="block text-sm font-semibold text-black mb-1">Tahun</label>
        <div class="relative">
          <select id="year" name="year"
                  class="w-full h-11 p-2 pr-9 rounded-md bg-white text-black
                         border border-black/30 focus:border-red-600 focus:ring-2 focus:ring-red-200
                         appearance-none">
            <option value="">Semua Tahun</option>
            @foreach ($years as $year)
              <option value="{{ $year->id }}" @selected(request('year')==$year->id)>{{ $year->year }}</option>
            @endforeach
          </select>
          <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-5 w-5 text-black/60"
               fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </div>
      </div>

      {{-- Tombol --}}
      <button type="submit"
              class="col-span-2 md:col-span-1 mt-2 w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-md
                     transition-transform hover:scale-105 shadow-md h-11 border border-red-700">
        Cari
      </button>
    </form>
  </div>
</section>
