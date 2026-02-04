@if($banners->isNotEmpty())
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 w-full my-4 px-4 md:px-8">
    @foreach ($banners->take(3) as $banner)
        <div class="relative w-full h-[250px] rounded-xl overflow-hidden shadow-md">
            <img src="{{ asset('storage/' . $banner->image) }}" 
                 alt="{{ $banner->title ?? 'Banner' }}" 
                 class="w-full h-full object-cover rounded-xl">

            {{-- Overlay full blur tipis + teks kecil di pojok kiri --}}
            <div class="absolute inset-0 bg-black/30"></div>

            {{-- @if(!empty($banner->title))
            <div class="absolute bottom-2 left-2">
                <div class="px-2 py-1">
                    <h1 class="text-xs md:text-sm font-semibold text-white drop-shadow ">
                        {{ $banner->title }}
                    </h1>
                    @if(!empty($banner->subtitle))
                    <p class="text-[10px] md:text-xs text-gray-200">
                        {{ $banner->subtitle }}
                    </p>
                    @endif
                </div>
            </div>
            @endif --}}
        </div>
    @endforeach
</div>
@else
{{-- fallback --}}
<div class="w-full bg-gray-200 dark:bg-gray-700 mb-10 px-4 md:px-8 py-12 text-center">
    <h2 class="text-2xl md:text-3xl font-bold text-gray-700 dark:text-gray-100">Lampegan Motor</h2>
    <p class="text-gray-600 dark:text-gray-300 mt-2">Promo & Informasi Banner</p>
</div>
@endif
