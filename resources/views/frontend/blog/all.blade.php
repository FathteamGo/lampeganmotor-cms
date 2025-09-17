@extends('layouts.app')

@section('title', 'Semua Blog - ' . $header->site_name)

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-2 py-12">
        <div class="max-w-4xl mx-auto">
            {{-- Header --}}
            <div class="text-center mb-12">
                <h2 class="text-2xl font-extrabold text-center text-black mb-6 inline-block border-b-4 border-yellow-400 px-2">
                    Semua Blog
                </h2>
                <p class="text-lg text-gray-600">
                    Temukan artikel menarik seputar otomotif, tips perawatan motor, dan informasi terbaru dari {{ $header->site_name }}
                </p>
            </div>

            {{-- Blog Cards - Same style as homepage --}}
            <div class="max-w-4xl mx-auto mb-12">
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
                                @if($blog->category && $blog->category->name)
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
            </div>

            {{-- Pagination --}}
            @if($blogs->hasPages())
                <div class="mt-6">
                    {{ $blogs->links('vendor.pagination.tailwind') }}
                </div>
            @endif

            {{-- Back to Home Button --}}
            <div class="text-center mt-12">
                <a href="{{ route('landing.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition duration-300 shadow-lg hover:shadow-xl">
                    <svg class="mr-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Custom CSS untuk line-clamp jika belum ada --}}
@push('styles')
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush
@endsection