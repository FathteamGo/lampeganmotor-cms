@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

$vehicleName   = $vehicle->displayName;
$pageTitle     = $vehicleName . ' - Lampegan Motor';

$whatsappNumber  = config('contact.whatsapp', '6281394510605');
$whatsappMessage = urlencode("Halo, saya tertarik dengan motor {$vehicleName}");
$whatsappLink    = "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}";

$photos        = $vehicle->photos;
$fallbackImage = asset('Images/logo/lampegan.png'); // pastikan folder di server sesuai
@endphp

@section('title', $pageTitle)

@section('content')
<div class="bg-white text-black dark:text-white min-h-screen pb-24 pt-4">

  {{-- Back link --}}
  <a href="{{ route('landing.index') }}"
     class="inline-flex items-center text-red-600 hover:text-yellow-500 mb-4 font-semibold transition-colors">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
    </svg>
    Kembali ke Beranda
  </a>

  <div class="grid grid-cols-1 gap-6">

    {{-- Slider Foto --}}
    @if($photos->isNotEmpty())
      <div x-data="{
            activeSlide: 0,
            slides: {{ $photos->map(fn($p, $i) => ['id'=>$i,'url'=>Storage::url($p->path)])->toJson() }}
          }" class="relative w-full">

        <div class="relative bg-white dark:bg-black rounded-lg overflow-hidden shadow-md aspect-w-4 aspect-h-3 ring-2 ring-yellow-400">
          @foreach($photos as $photo)
            <div x-show="activeSlide === {{ $loop->index }}"
                 class="w-full h-full transition-opacity duration-300"
                 x-transition:enter="ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
              <img src="{{ Storage::url($photo->path) }}"
                   alt="{{ $vehicleName }} - Gambar {{ $loop->iteration }}"
                   class="w-full h-full object-cover"
                   onerror="this.onerror=null;this.src='{{ $fallbackImage }}';" />
            </div>
          @endforeach
        </div>

        @if($photos->count() > 1)
          <div class="absolute inset-0 flex items-center justify-between px-2">
            <button @click="activeSlide = (activeSlide - 1 + slides.length) % slides.length"
                    class="bg-black/40 hover:bg-yellow-500/70 p-2 rounded-full text-white transition">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>
            <button @click="activeSlide = (activeSlide + 1) % slides.length"
                    class="bg-black/40 hover:bg-yellow-500/70 p-2 rounded-full text-white transition">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
          </div>

          <div class="grid grid-cols-4 sm:grid-cols-5 gap-2 mt-3">
            @foreach($photos as $photo)
              <button type="button"
                      @click="activeSlide = {{ $loop->index }}"
                      :class="{ 'ring-2 ring-yellow-400 ring-offset-2': activeSlide === {{ $loop->index }} }"
                      class="aspect-w-1 aspect-h-1 rounded-md overflow-hidden">
                <img src="{{ Storage::url($photo->path) }}"
                     alt="Thumbnail {{ $loop->iteration }}"
                     class="w-full h-full object-cover"
                     onerror="this.onerror=null;this.src='{{ $fallbackImage }}';" />
              </button>
            @endforeach
          </div>
        @endif
      </div>
    @else
      <div class="bg-white dark:bg-black rounded-lg overflow-hidden shadow-md ring-2 ring-yellow-400">
        <div class="aspect-w-4 aspect-h-3">
          <img src="{{ $fallbackImage }}" alt="Gambar tidak tersedia" class="w-full h-full object-cover">
        </div>
      </div>
    @endif

    {{-- Detail Kendaraan --}}
    <section class="bg-white dark:bg-black p-4 rounded-lg shadow-md">
      <h1 class="text-2xl font-extrabold mb-1 text-white-500">{{ $vehicleName }}</h1>
      <p class="text-2xl font-bold text-red-600 mb-3">
        {{ Number::currency($vehicle->sale_price, 'IDR', 'id') }}
      </p>
      <p class="text-sm text-black/70 dark:text-white/70 mb-4">
        Tahun {{ $vehicle->year->year ?? 'N/A' }}
      </p>

      <h3 class="text-lg font-bold mt-4 mb-3 border-b-2 border-yellow-500 pb-2">Spesifikasi</h3>
      <div class="space-y-2 text-black dark:text-white">
        <div class="flex justify-between gap-3"><strong>Mesin</strong><span class="text-right font-mono">{{ strip_tags($vehicle->engine_specification) ?? 'N/A' }}</span></div>
        <div class="flex justify-between gap-3"><strong>Tipe</strong><span class="text-right font-mono">{{ $vehicle->type->name ?? 'N/A' }}</span></div>
        <div class="flex justify-between gap-3"><strong>Lokasi</strong><span class="text-right font-mono">{{ $vehicle->location ?? 'N/A' }}</span></div>
        <div class="flex justify-between gap-3"><strong>Diposting</strong><span class="text-right font-mono">{{ $vehicle->created_at->translatedFormat('d F Y') }}</span></div>
      </div>

      @if($vehicle->description)
        <h3 class="text-lg font-bold mt-5 mb-3 border-b-2 border-yellow-500 pb-2">Deskripsi</h3>
        <div class="prose dark:prose-invert max-w-none text-black dark:text-white leading-relaxed">
          {!! $vehicle->description !!}
        </div>
      @endif

      @if($vehicle->dp_percentage > 0)
        <h3 class="text-lg font-bold mt-5 mb-3 border-b-2 border-yellow-500 pb-2">Skema Pembayaran</h3>
        <p class="text-black dark:text-white leading-relaxed">
          Cukup bayar DP mulai dari
          <strong class="text-yellow-500">{{ Number::currency($vehicle->sale_price * ($vehicle->dp_percentage / 100), 'IDR', 'id') }}</strong>.
          Info lebih lanjut silakan hubungi kami via WhatsApp.
        </p>
      @endif

      @php
        $admins = \App\Models\WhatsAppNumber::where('is_active', true)->get();
      @endphp

      {{-- CTA WhatsApp --}}
      <div class="mt-5" x-data="{ open: false }">
        <button @click="open = true"
          class="w-full bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600
               text-white font-semibold py-3 px-5 rounded-lg flex items-center justify-center gap-2
               shadow-md transition duration-200 cursor-pointer">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" class="w-6 h-6 fill-current">
            <path d="M128 0C57.31 0 0 56.57 0 126.46c0 22.32 5.93 43.32 16.23 61.51L0 256l70.25-22.81c17.94 9.82 38.49 15.42 60.4 15.42 70.69 0 128-56.57 128-126.46S198.69 0 128 0zm0 230.77c-19.33 0-37.3-5.42-52.53-14.79l-3.74-2.27-41.68 13.53 13.55-40.2-2.44-3.91C33.01 168.38 26.46 147.84 26.46 126.46 26.46 69.84 72.55 23.73 128 23.73s101.54 46.11 101.54 102.73S183.45 230.77 128 230.77zM187.63 150.3c-3.19-1.59-18.88-9.27-21.81-10.34-2.93-1.07-5.07-1.59-7.2 1.6-2.13 3.19-8.27 10.34-10.14 12.47-1.87 2.13-3.73 2.4-6.92.8-3.19-1.59-13.46-4.97-25.64-15.83-9.47-8.41-15.87-18.79-17.74-21.98-1.87-3.19-.2-4.92 1.4-6.52 1.43-1.43 3.19-3.73 4.79-5.59 1.6-1.86 2.13-3.19 3.19-5.32 1.07-2.13.53-3.99-.27-5.59-.8-1.59-7.2-17.36-9.87-23.74-2.6-6.24-5.25-5.39-7.2-5.48l-6.16-.11c-2.13 0-5.59.8-8.52 3.99-2.93 3.19-11.2 10.94-11.2 26.69 0 15.74 11.46 30.95 13.06 33.08 1.6 2.13 22.51 34.38 54.53 48.16 7.62 3.29 13.55 5.25 18.19 6.72 7.63 2.42 14.57 2.08 20.07 1.26 6.12-.91 18.88-7.72 21.53-15.18 2.67-7.46 2.67-13.85 1.87-15.18-.8-1.33-2.93-2.13-6.12-3.72z"/>
          </svg>
          <span>Tanya & Nego via WA</span>
        </button>


        @php
          $owners = \App\Models\WhatsAppNumber::where('is_active', true)
              ->whereHas('user', fn ($q) => $q->where('role', 'owner'))
              ->get();

          $admins = \App\Models\WhatsAppNumber::where('is_active', true)
              ->whereHas('user', fn ($q) => $q->where('role', 'admin'))
              ->get();
        @endphp
      <!-- Modal -->
     <template x-if="open">
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 text-black">
            <div class="bg-white rounded-lg w-80 p-6 relative">
                <h3 class="text-lg font-bold mb-4">Hubungi Kami</h3>
                <ul class="space-y-2 mb-4">
                    @foreach($owners->merge($admins) as $contact)
                        <li>
                            <a href="https://wa.me/{{ $contact->number }}" target="_blank"
                               class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 256 256">
                                    <path d="M128 0C57.31 0 0 56.57 0 126.46c0 22.32 5.93 43.32 16.23 61.51L0 256l70.25-22.81c17.94 9.82 38.49 15.42 60.4 15.42 70.69 0 128-56.57 128-126.46S198.69 0 128 0zm0 230.77c-19.33 0-37.3-5.42-52.53-14.79l-3.74-2.27-41.68 13.53 13.55-40.2-2.44-3.91C33.01 168.38 26.46 147.84 26.46 126.46 26.46 69.84 72.55 23.73 128 23.73s101.54 46.11 101.54 102.73S183.45 230.77 128 230.77zM187.63 150.3c-3.19-1.59-18.88-9.27-21.81-10.34-2.93-1.07-5.07-1.59-7.2 1.6-2.13 3.19-8.27 10.34-10.14 12.47-1.87 2.13-3.73 2.4-6.92.8-3.19-1.59-13.46-4.97-25.64-15.83-9.47-8.41-15.87-18.79-17.74-21.98-1.87-3.19-.2-4.92 1.4-6.52 1.43-1.43 3.19-3.73 4.79-5.59 1.6-1.86 2.13-3.19 3.19-5.32 1.07-2.13.53-3.99-.27-5.59-.8-1.59-7.2-17.36-9.87-23.74-2.6-6.24-5.25-5.39-7.2-5.48l-6.16-.11c-2.13 0-5.59.8-8.52 3.99-2.93 3.19-11.2 10.94-11.2 26.69 0 15.74 11.46 30.95 13.06 33.08 1.6 2.13 22.51 34.38 54.53 48.16 7.62 3.29 13.55 5.25 18.19 6.72 7.63 2.42 14.57 2.08 20.07 1.26 6.12-.91 18.88-7.72 21.53-15.18 2.67-7.46 2.67-13.85 1.87-15.18-.8-1.33-2.93-2.13-6.12-3.72z"/>
                                </svg>
                                {{ $contact->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                <button @click="open = false" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">&times;</button>
            </div>
        </div>
    </template>
      </div>

    </section>
  </div>
</div>
@endsection
