@if($banners->isNotEmpty())
<div class="grid grid-cols-1 gap-4 max-w-3xl mx-auto my-4">
    @foreach ($banners->take(3) as $banner)
        <div class="relative w-full h-[250px] rounded-xl overflow-hidden shadow-md">
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
</div>
@else
{{-- fallback --}}
<div class="rounded-xl bg-gray-200 dark:bg-gray-700 mb-10 px-6 py-12 text-center max-w-3xl mx-auto">
    <h2 class="text-2xl md:text-3xl font-bold text-gray-700 dark:text-gray-100">Lampegan Motor</h2>
    <p class="text-gray-600 dark:text-gray-300 mt-2">Promo & Informasi Banner</p>
</div>
@endif
