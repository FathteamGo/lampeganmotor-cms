<div class="container mx-auto px-4 py-8">
     <h2 class="text-2xl font-extrabold text-center text-black mb-6 inline-block border-b-4 border-yellow-400 px-2">
      Blog Lampegan
    </h2>

    @forelse($blogs as $blog)
        @if ($loop->first)
            <div class="swiper blog-swiper">
                <div class="swiper-wrapper">
        @endif

        <div class="swiper-slide">
            <div class="bg-white rounded-lg shadow hover:shadow-xl overflow-hidden flex flex-col">
                @if($blog->cover_image)
                    <div class="relative overflow-hidden h-48">
                        <img src="{{ asset('storage/'.$blog->cover_image) }}" 
                             alt="{{ $blog->title }}" 
                             class="w-full h-full object-cover transform hover:scale-105 transition-transform duration-300">
                        @if(isset($blog->category->name))
                            <span class="absolute top-2 left-2 bg-blue-600 text-white text-xs font-semibold px-2 py-1 rounded">
                                {{ $blog->category->name }}
                            </span>
                        @endif
                    </div>
                @endif
                <div class="p-4 flex flex-col justify-between flex-1">
                    <h4 class="text-lg font-semibold mb-2 hover:text-blue-600 transition-colors duration-200">
                        {{ $blog->title }}
                    </h4>
                    <p class="text-gray-600 text-sm">{{ Str::limit($blog->content, 120) }}</p>
                    <div class="mt-4 flex justify-between items-center">
                        <span class="text-gray-400 text-xs">{{ $blog->created_at->format('d M Y') }}</span>
                        <a href="{{ route('blog.show', $blog->slug) }}" class="bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded hover:bg-blue-700 transition-colors duration-200">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if ($loop->last)
                </div> <!-- /.swiper-wrapper -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div> <!-- /.swiper -->
        @endif

    @empty
        <p class="text-gray-500 text-center mt-6">Tidak ada blog untuk saat ini.</p>
    @endforelse
</div>
