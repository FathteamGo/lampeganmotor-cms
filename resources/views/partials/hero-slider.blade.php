@php
    $slides = collect($heroSlides ?? []);
@endphp

@if($slides->isNotEmpty())
<div id="hero-slider" 
     class="relative w-full h-[250px] md:h-[400px] lg:h-[500px] overflow-hidden shadow-md mb-8">

    @foreach ($slides as $slide)
        <div class="hero-slide {{ $loop->first ? 'slide-active' : 'slide-inactive' }} 
                    absolute inset-0 transition-opacity duration-1000 ease-in-out">

            {{-- Ambil gambar (cek array/string) --}}
            <img src="{{ is_array($slide->image) 
                            ? (isset($slide->image[0]) ? Storage::url($slide->image[0]) : asset('images/no-image.png')) 
                            : ($slide->image ? Storage::url($slide->image) : asset('images/no-image.png')) }}" 
                 alt="{{ $slide->title ?? 'Slide' }}" 
                 class="w-full h-full object-cover">

            {{-- Overlay teks --}}
            <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                <div class="text-center text-white p-4">
                    @if(!empty($slide->title))
                        <h1 class="text-xl md:text-2xl font-bold mb-1 drop-shadow-lg">
                            {{ $slide->title }}
                        </h1>
                    @endif
                    @if(!empty($slide->subtitle))
                        <p class="text-sm md:text-base drop-shadow-md">
                            {{ $slide->subtitle }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    {{-- Tombol navigasi --}}
    <button id="prev-slide" 
            class="absolute top-1/2 left-2 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 p-2 rounded-full text-white transition">
        &lt;
    </button>
    <button id="next-slide" 
            class="absolute top-1/2 right-2 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 p-2 rounded-full text-white transition">
        &gt;
    </button>
</div>
@else
{{-- fallback kalau tidak ada slide --}}
<div class="rounded-xl bg-gray-200 dark:bg-gray-700 mb-10 px-6 py-12 text-center max-w-lg mx-auto">
    <h2 class="text-2xl md:text-3xl font-bold text-gray-700 dark:text-gray-100">Lampegan Motor</h2>
    <p class="text-gray-600 dark:text-gray-300 mt-2">Galeri & Form Jual Motor</p>
</div>
@endif
