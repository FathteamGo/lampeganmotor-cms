@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
@endphp

@section('title', 'Galeri Motor - Lampegan Motor')

@section('content')
<div class="bg-white text-black dark:text-white pb-20 md:pb-8">

    {{-- Hero Slider --}}
    @include('partials.hero-slider')

    {{-- Video Section --}}
    @include('partials.video')

    {{-- Banner Section --}}
    @include('partials.banner')

    {{-- Blog Section --}}
    @include('partials.blog_section', ['categories_blog' => $categories_blog, 'blogs' => $blogs])

    {{-- Filter Section --}}
    @include('partials.filter')

    {{-- Konten Galeri --}}
    <div class="mx-auto w-full px-4 md:px-8">
        @if ($vehicles->isEmpty())
            <p class="text-center text-lg text-black dark:text-white">
                Tidak ada motor yang ditemukan dengan filter yang dipilih.
            </p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($vehicles as $vehicle)
                    @php
                        $isSold = $vehicle->status === 'sold';
                    @endphp

                    <{{ $isSold ? 'div' : 'a' }}
                        @unless($isSold)
                            href="{{ route('landing.show', $vehicle) }}"
                        @endunless
                        class="relative block bg-white dark:bg-black rounded-lg shadow-md hover:shadow-xl
                               transition-all duration-300 overflow-hidden group
                               {{ $isSold ? 'opacity-95 cursor-not-allowed' : '' }}"
                    >
                        {{-- Gambar dengan tinggi tetap --}}
                        <div class="relative w-full h-64 overflow-hidden rounded-t-lg">
                            <img
                                src="{{ $vehicle->photos->first()
                                    ? Storage::url($vehicle->photos->first()->path)
                                    : asset('/Images/logo/lampegan.png') }}"
                                alt="{{ $vehicle->displayName }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                onerror="this.onerror=null;this.src='{{ asset('Images/logo/lampegan.png') }}';"
                            />

                            {{-- Stempel TERJUAL di atas gambar --}}
                            @if ($isSold)
    <div class="absolute inset-0 bg-black/20 flex items-center justify-center">
        <span class="text-white text-4xl font-extrabold uppercase tracking-widest
                     px-8 py-3 rounded-md border-4 border-white bg-white/20
                     shadow-[0_0_20px_rgba(255,255,255,0.7)]
                     rotate-[-18deg] select-none"
              style="text-shadow: 2px 2px 8px rgba(0,0,0,0.6);">
            TERJUAL
        </span>
    </div>
@endif
                        </div>

                        {{-- Detail Kendaraan --}}
                        <div class="p-4 flex flex-col">
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="text-lg font-semibold text-black dark:text-white
                                           group-hover:text-red-500 transition-colors">
                                    {{ $vehicle->displayName }}
                                </h3>

                                {{-- Label TERJUAL di samping nama --}}
                                @if ($isSold)
                                    <span class="text-xs font-bold uppercase bg-red-600 text-white px-3 py-1 rounded-md
                                                shadow-sm border border-red-700">
                                        TERJUAL
                                    </span>
                                @endif
                            </div>

                            @php
                                // Avoid null passing into Number::currency when sale_price is missing
                                $price = $vehicle->sale_price;
                                $formattedPrice = $price !== null
                                    ? Number::currency($price, 'IDR', 'id')
                                    : 'Harga belum tersedia';
                            @endphp

                            <p class="text-sm text-black dark:text-white mb-2">
                                Tahun {{ $vehicle->year->year ?? 'N/A' }}
                            </p>
                            <p class="text-xl font-bold text-red-600 dark:text-red-500">
                                {{ $formattedPrice }}
                            </p>
                        </div>
                    </{{ $isSold ? 'div' : 'a' }}>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $vehicles->links('vendor.pagination.tailwind') }}
            </div>
        @endif
    </div>
</div>

@stack('scripts')
<script>
    // Hero Slider Script
    document.addEventListener('DOMContentLoaded', function() {
        const slider = document.getElementById('hero-slider');
        if (!slider) return;

        const slides = Array.from(slider.querySelectorAll('.hero-slide'));
        const prevButton = document.getElementById('prev-slide');
        const nextButton = document.getElementById('next-slide');
        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.remove('slide-active', 'slide-inactive');
                if (i === index) slide.classList.add('slide-active');
                else slide.classList.add('slide-inactive');
            });
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(currentSlide);
        }

        prevButton?.addEventListener('click', prevSlide);
        nextButton?.addEventListener('click', nextSlide);

        // Auto slide every 5s
        let autoSlideInterval = setInterval(nextSlide, 5000);

        // Pause on hover
        slider.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
        slider.addEventListener('mouseleave', () => {
            autoSlideInterval = setInterval(nextSlide, 5000);
        });

        showSlide(currentSlide);
    });
</script>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash === '#filter-section') {
            const filterElement = document.getElementById('filter-section');
            filterElement?.scrollIntoView({ behavior: 'smooth' });
        }
    });
</script>
@endpush
