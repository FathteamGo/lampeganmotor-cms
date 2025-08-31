@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

// Menggunakan accessor displayName() dari model untuk konsistensi
$vehicleName = $vehicle->displayName;
$pageTitle = $vehicleName . ' - Lampegan Motor';

// [OK] Nomor WA diambil dari config, dengan fallback
$whatsappNumber = config('contact.whatsapp', '6281394510605');
$whatsappMessage = urlencode("Halo, saya tertarik dengan motor {$vehicleName}");
$whatsappLink = "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}";

$photos = $vehicle->photos;
$fallbackImage = asset('assets/images/placeholder.jpg'); // Pastikan Anda punya gambar ini
@endphp

@section('title', $pageTitle)

@section('content')
{{-- [DIPERBAIKI] Menambahkan padding bottom (pb-20) untuk memberi ruang navigasi bawah --}}
<div class="bg-gray-50 text-gray-900 dark:text-white min-h-screen pb-20">
    <div class="container mx-auto max-w-md px-4 py-8">
        <a href="{{ route('landing.index') }}"
            class="inline-flex items-center text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-500 mb-8 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            Kembali ke Galeri
        </a>
        <div class="grid grid-cols-1 gap-8">

            {{-- [DIPERBARUI] Galeri Gambar sekarang menjadi Slider --}}
            @if($photos->isNotEmpty())
            <div x-data="{
                        activeSlide: 0,
                        slides: {{ $photos->map(fn($p, $i) => ['id' => $i, 'url' => Storage::url($p->path)])->toJson() }}
                     }"
                class="relative w-full">

                <div class="relative bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-2xl aspect-w-4 aspect-h-3">
                    @foreach($photos as $photo)
                    <div x-show="activeSlide === {{ $loop->index }}"
                        class="w-full h-full transition-opacity duration-300"
                        x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                        <img src="{{ Storage::url($photo->path) }}" alt="{{ $vehicleName }} - Gambar {{ $loop->iteration }}" class="w-full h-full object-cover">
                    </div>
                    @endforeach
                </div>

                @if($photos->count() > 1)
                <div class="absolute inset-0 flex items-center justify-between px-2">
                    <button @click="activeSlide = (activeSlide - 1 + slides.length) % slides.length"
                        class="bg-black/30 hover:bg-black/50 p-2 rounded-full text-white transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button @click="activeSlide = (activeSlide + 1) % slides.length"
                        class="bg-black/30 hover:bg-black/50 p-2 rounded-full text-white transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
                @endif

                @if($photos->count() > 1)
                <div class="grid grid-cols-4 sm:grid-cols-5 gap-2 mt-4">
                    @foreach ($photos as $photo)
                    <div @click="activeSlide = {{ $loop->index }}"
                        :class="{ 'ring-2 ring-red-500 ring-offset-2 dark:ring-offset-gray-800': activeSlide === {{ $loop->index }} }"
                        class="aspect-w-1 aspect-h-1 rounded-md cursor-pointer overflow-hidden">
                        <img src="{{ Storage::url($photo->path) }}" alt="Thumbnail {{ $loop->iteration }}" class="w-full h-full object-cover">
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @else
            {{-- Fallback jika tidak ada gambar sama sekali --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-2xl">
                <div class="aspect-w-4 aspect-h-3">
                    <img src="{{ $fallbackImage }}" alt="Gambar tidak tersedia" class="w-full h-full object-cover">
                </div>
            </div>
            @endif

            {{-- Vehicle Details --}}
            <div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl">
                    <h1 class="text-3xl font-extrabold mb-2">
                        {{ $vehicleName }}
                    </h1>
                    <p class="text-3xl font-bold text-red-500 mb-6">
                        {{ Number::currency($vehicle->sale_price, 'IDR', 'id') }}
                    </p>
                    <p class="text-md text-gray-500 dark:text-gray-400 mb-4">
                        Tahun {{ $vehicle->year->year ?? 'N/A' }}
                    </p>

                    <h3 class="text-xl font-bold mt-8 mb-4 border-b-2 border-red-500 pb-2">Spesifikasi</h3>
                    <div class="space-y-3 text-gray-600 dark:text-gray-300">
                        <div class="flex justify-between items-center"><strong>Mesin</strong> <span class="text-right font-mono">{{ strip_tags($vehicle->engine_specification) ?? 'N/A' }}</span></div>
                        <div class="flex justify-between items-center"><strong>Tipe</strong> <span class="text-right font-mono">{{ $vehicle->type->name ?? 'N/A' }}</span></div>
                        <div class="flex justify-between items-center"><strong>Lokasi</strong> <span class="text-right font-mono">{{ $vehicle->location  ?? 'N/A' }}</span></div>
                        <div class="flex justify-between items-center"><strong>Diposting</strong> <span class="text-right font-mono">{{ $vehicle->created_at->translatedFormat('d F Y') }}</span></div>
                    </div>

                    @if($vehicle->description)
                    <h3 class="text-xl font-bold mt-8 mb-4 border-b-2 border-red-500 pb-2">Deskripsi</h3>
                    <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed">
                        {!! $vehicle->description !!}
                    </div>
                    @endif

                    @if($vehicle->dp_percentage > 0)
                    <h3 class="text-xl font-bold mt-8 mb-4 border-b-2 border-red-500 pb-2">Skema Pembayaran</h3>
                    <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed">
                        Cukup bayar DP mulai dari {{ Number::currency($vehicle->sale_price * ($vehicle->dp_percentage / 100), 'IDR', 'id') }}. Info lebih lanjut silahkan hubungi kami via WhatsApp.
                    </div>
                    @endif
                </div>

                <div class="mt-6">
                    <a href="{{ $whatsappLink }}" target="_blank"
                        class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-4 rounded-lg flex items-center justify-center text-lg shadow-lg transition-transform transform hover:scale-105">
                        <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.894 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.886-.001 2.269.655 4.357 1.849 6.081l-1.214 4.439 4.572-1.21zM9.06 8.928c-.09-.158-.297-.25-.504-.25h-.17c-.183 0-.363.044-.519.138-.158.095-.319.228-.445.404-.126.176-.181.375-.181.574s.054.398.181.573c.126.176.287.309.445.403.156.095.336.139.519.139h.17c.207 0 .414-.092.504-.25.09-.158.138-.354.138-.549s-.048-.391-.138-.549zM11.191 11.449c-.191.333-.42.613-.69.828-.27.215-.578.348-.901.39-.323.043-.654-.006-.957-.108s-.58-.25-.799-.438c-.219-.188-.411-.411-.57-.66-.16-.249-.288-.522-.379-.811-.091-.289-.137-.591-.137-.899s.045-.609.137-.899c.091-.289.219-.562.379-.81.16-.249.351-.472.57-.66.219-.188.481-.343.799-.437.303-.102.634-.151.957-.108.323.043.631.175.901.39.27.215.5.495.69.828.191.334.287.702.287 1.071s-.096.737-.287 1.071zM15.463 8.928c-.09-.158-.297-.25-.504-.25h-.17c-.183 0-.363.044-.519.138-.158.095-.319.228-.445.404-.126.176-.181.375-.181.574s.054.398.181.573c.126.176.287.309.445.403.156.095.336.139.519.139h.17c.207 0 .414-.092.504-.25.09-.158.138-.354.138-.549s-.048-.391-.138-.549z" />
                        </svg>
                        Tanya & Nego via WA
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection