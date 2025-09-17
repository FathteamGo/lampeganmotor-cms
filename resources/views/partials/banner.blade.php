@if($banners->isNotEmpty())
<div id="banner-slider" 
     class="relative w-full max-w-3xl mx-auto h-[250px] overflow-hidden rounded-xl shadow-md my-4"
     x-data="{ index: 0 }"
     x-init="setInterval(() => { index = (index + 1) % {{ $banners->count() }} }, 5000)">
    
    @foreach ($banners as $i => $banner)
        <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
             :class="index === {{ $i }} ? 'opacity-100 z-10' : 'opacity-0 z-0'">
            <img src="{{ asset('storage/' . $banner->image) }}" 
                 alt="{{ $banner->title ?? 'Banner' }}" 
                 class="w-full h-full object-cover rounded-xl">

            {{-- Overlay teks --}}
            @if(!empty($banner->title))
            <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                <div class="text-center text-white p-4">
                    <h1 class="text-xl md:text-2xl font-bold mb-1 drop-shadow-lg">
                        {{ $banner->title }}
                    </h1>
                    @if(!empty($banner->subtitle))
                    <p class="text-sm md:text-base drop-shadow-md">
                        {{ $banner->subtitle }}
                    </p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    @endforeach

    {{-- Tombol navigasi --}}
    <button @click="index = (index - 1 + {{ $banners->count() }}) % {{ $banners->count() }}" 
            class="absolute top-1/2 left-2 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 p-2 rounded-full text-white transition">
        &lt;
    </button>
    <button @click="index = (index + 1) % {{ $banners->count() }}" 
            class="absolute top-1/2 right-2 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 p-2 rounded-full text-white transition">
        &gt;
    </button>
</div>
@else
{{-- fallback --}}
<div class="rounded-xl bg-gray-200 dark:bg-gray-700 mb-10 px-6 py-12 text-center max-w-3xl mx-auto">
    <h2 class="text-2xl md:text-3xl font-bold text-gray-700 dark:text-gray-100">Lampegan Motor</h2>
    <p class="text-gray-600 dark:text-gray-300 mt-2">Promo & Informasi Banner</p>
</div>
@endif
