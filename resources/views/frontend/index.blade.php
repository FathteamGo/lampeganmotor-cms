@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
@endphp

@section('title', 'Galeri Motor - Lampegan Motor')

@section('content')
<div class="bg-white text-black dark:text-white pb-20">

    {{-- Hero Slider --}}
    @include('partials.hero-slider')

    {{-- Video Section --}}
    @include('partials.video')

    {{-- Banner Section --}}
    @include('partials.banner')

     @include('partials.blog_section', ['categories_blog' => $categories_blog, 'blogs' => $blogs])

    {{-- Filter Section --}}
    @include('partials.filter')

    {{-- Konten Galeri --}}
    <div class="mx-auto max-w-sm px-4">
        @if ($vehicles->isEmpty())
            <p class="text-center text-lg text-black dark:text-white">
                Tidak ada motor yang ditemukan dengan filter yang dipilih.
            </p>
        @else
            <div class="grid grid-cols-1 gap-6">
                @foreach ($vehicles as $vehicle)
                    <a href="{{ route('landing.show', $vehicle) }}"
                       class="block bg-white dark:bg-black rounded-lg shadow-md hover:shadow-xl 
                              transition-all duration-300 overflow-hidden group">
                        {{-- Gambar kotak --}}
                        <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden">
                          <img
                                src="{{ $vehicle->photos->first() ? Storage::url($vehicle->photos->first()->path) : asset('/Images/logo/lampegan.png') }}"
                                alt="{{ $vehicle->displayName }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                onerror="this.onerror=null;this.src='{{ asset('Images/logo/lampegan.png') }}';"
                            />
                        </div>

                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-black dark:text-white mb-1 
                                       group-hover:text-red-500 transition-colors">
                                {{ $vehicle->displayName }}
                            </h3>
                            <p class="text-sm text-black dark:text-white mb-2">
                                Tahun {{ $vehicle->year->year ?? 'N/A' }}
                            </p>
                            <p class="text-xl font-bold text-red-600 dark:text-red-500">
                                {{ Number::currency($vehicle->sale_price, 'IDR', 'id') }}
                            </p>
                        </div>
                    </a>
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
                if (i === index) {
                    slide.classList.add('slide-active');
                } else {
                    slide.classList.add('slide-inactive');
                }
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

        // Auto slide
        let autoSlideInterval = setInterval(nextSlide, 5000);

        // Pause auto slide on hover
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
