@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
@endphp

@section('title', 'Galeri Motor - Lampegan Motor')

@section('content')
<div class="bg-gray-50  text-gray-900 dark:text-white pb-20">
    {{-- Hero Slider --}}
    @include('partials.hero-slider')

    {{-- Video Section --}}
    @include('partials.video')

    {{-- Filter Section --}}
    @include('partials.filter')


    <div class="container mx-auto max-w-lg px-4 sm:px-6 lg:px-8">
        @if ($vehicles->isEmpty())
        <p class="text-center text-xl text-gray-600 dark:text-gray-400">Tidak ada motor yang ditemukan dengan filter yang dipilih.</p>
        @else
        <div class="grid grid-cols-1 gap-6">
            @foreach ($vehicles as $vehicle)
            <a href="{{ route('landing.show', $vehicle) }}" class="block bg-white dark:bg-gray-800 rounded-lg shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden group">
                <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden"> {{-- Gambar kotak --}}
                    <img src="{{ $vehicle->photos->first() ? Storage::url($vehicle->photos->first()->path) : asset('assets/images/placeholder.jpg') }}"
                        alt=" {{ $vehicle->displayName }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
                <div class="p-4">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-1 group-hover:text-red-500 transition-colors">
                        {{ $vehicle->displayName }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-3">Tahun {{ $vehicle->year->year ?? 'N/A' }}</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-500">
                        {{ Number::currency($vehicle->sale_price, 'IDR', 'id') }}
                    </p>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $vehicles->links() }}
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

        nextButton.addEventListener('click', nextSlide);
        prevButton.addEventListener('click', prevSlide);

        // Auto slide
        let autoSlideInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds

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
        // Cek apakah URL memiliki hash #filter-section saat halaman dimuat
        if (window.location.hash === '#filter-section') {
            // Cari elemennya
            const filterElement = document.getElementById('filter-section');
            if (filterElement) {
                // Lakukan scroll ke elemen tersebut
                filterElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        }
    });
</script>
@endpush