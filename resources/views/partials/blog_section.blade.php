<div class="container mx-auto px-2 py-12">
   <h2 class="text-2xl font-extrabold text-center text-black mb-6 inline-block border-b-4 border-yellow-400 px-2">
      Blog Terbaru
    </h2>

    @forelse($blogs as $blog)
        @if ($loop->first)
            <div class="swiper blog-swiper">
                <div class="swiper-wrapper">
        @endif

        {{-- Card compact ala artikel terkait --}}
        <div class="swiper-slide py-4">
            <a href="{{ route('blog.show', $blog->slug) }}" 
               class="bg-white rounded-xl shadow hover:shadow-lg overflow-hidden transition border border-gray-100 flex flex-col">
                @if($blog->cover_image)
                    <img src="{{ asset('storage/'.$blog->cover_image) }}" 
                         alt="{{ $blog->title }}" 
                         class="h-40 w-full object-cover">
                @endif
                <div class="p-4 flex flex-col flex-1">
                    @if(isset($blog->category->name))
                        <span class="inline-block bg-gradient-to-r from-blue-600 to-blue-500 text-white text-xs font-semibold px-3 py-1 rounded-full shadow mb-3">
                            {{ $blog->category->name }}
                        </span>
                    @endif
                    <h4 class="font-semibold text-lg text-gray-800 mb-2 line-clamp-2">
                        {{ $blog->title }}
                    </h4>
                    <p class="text-sm text-gray-500 line-clamp-3 flex-1">
                        {{ Str::limit(strip_tags($blog->content), 100) }}
                    </p>
                    <p class="mt-3 text-xs text-gray-400">{{ $blog->created_at->format('d M Y') }}</p>
                </div>
            </a>
        </div>

        @if ($loop->last)
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        @endif

    @empty
        <p class="text-gray-500 text-center mt-6">Tidak ada blog untuk saat ini.</p>
    @endforelse
</div>
