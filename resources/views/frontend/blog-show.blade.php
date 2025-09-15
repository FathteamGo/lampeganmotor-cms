{{-- resources/views/blog/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12 max-w-4xl">
    
    {{-- Artikel Utama --}}
    <article>
        {{-- Cover --}}
        @if($blog->cover_image)
            <div class="relative mb-6">
                <img src="{{ asset('storage/'.$blog->cover_image) }}" 
                     alt="{{ $blog->title }}" 
                     class="w-full h-96 object-cover rounded-2xl">
                @if($blog->category?->name)
                    <span class="absolute top-4 left-4 bg-gradient-to-r from-blue-600 to-blue-500 text-white text-xs font-semibold px-3 py-1 rounded-full shadow">
                        {{ $blog->category->name }}
                    </span>
                @endif
            </div>
        @endif

        {{-- Judul --}}
        <h1 class="text-4xl font-extrabold text-gray-900 leading-tight mb-4">
            {{ $blog->title }}
        </h1>

        {{-- Info --}}
        <div class="flex items-center text-sm text-gray-500 mb-8">
            <span>Lampegan Motor Blog</span>
            <span class="mx-2">•</span>
            <span>{{ $blog->created_at->format('d M Y') }}</span>
        </div>

        {{-- Konten --}}
        <div class="prose prose-lg max-w-none text-gray-800 leading-relaxed">
            {!! $blog->content !!}
        </div>

        {{-- Tombol Share --}}
        <div class="mt-10 border-t pt-6 flex items-center justify-between">
            <a href="{{ route('landing.index') }}" 
               class="inline-flex items-center gap-2 text-gray-700 hover:text-blue-600 transition">
                ← Kembali ke Beranda
            </a>
        </div>
    </article>

    {{-- Related Posts --}}
    @if($relatedBlogs->count() > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Artikel Terkait</h2>
            <div class="grid md:grid-cols-2 gap-6">
                @foreach($relatedBlogs as $related)
                    <a href="{{ route('blog.show', $related->slug) }}" 
                       class="bg-white rounded-xl shadow hover:shadow-lg overflow-hidden transition border border-gray-100">
                        @if($related->cover_image)
                            <img src="{{ asset('storage/'.$related->cover_image) }}" alt="{{ $related->title }}" class="h-40 w-full object-cover">
                        @endif
                        <div class="p-4">
                            <h3 class="font-semibold text-lg text-gray-800 mb-2 line-clamp-2">
                                {{ $related->title }}
                            </h3>
                            <p class="text-sm text-gray-500">{{ $related->created_at->format('d M Y') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
