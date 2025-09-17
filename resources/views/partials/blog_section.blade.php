<div class="container mx-auto px-2 py-12">
    <h2 class="text-2xl font-extrabold text-center text-black mb-6 inline-block border-b-4 border-yellow-400 px-2">
        Blog Terbaru
    </h2>

    @forelse($blogs as $blog)
        <div class="py-4">
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
    @empty
        <p class="text-gray-500 text-center mt-6">Tidak ada blog untuk saat ini.</p>
    @endforelse

    {{-- Lihat Semua Button - Only show if there are more than 3 blogs --}}
    @if($blogs->count() >= 3)
        <div class="mt-8 text-center">
            <a href="{{ route('blog.all') }}" 
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-600 transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                <span>Lihat Semua Blog</span>
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    @endif
</div>