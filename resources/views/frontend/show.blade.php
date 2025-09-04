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
$fallbackImage = asset('assets/images/placeholder.jpg');
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
    Kembali ke Galeri
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
                 x-transition:leave="ease-in"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
              <img src="{{ Storage::url($photo->path) }}"
                   alt="{{ $vehicleName }} - Gambar {{ $loop->iteration }}"
                   class="w-full h-full object-cover">
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
            @foreach ($photos as $photo)
              <button type="button"
                      @click="activeSlide = {{ $loop->index }}"
                      :class="{ 'ring-2 ring-yellow-400 ring-offset-2': activeSlide === {{ $loop->index }} }"
                      class="aspect-w-1 aspect-h-1 rounded-md overflow-hidden">
                <img src="{{ Storage::url($photo->path) }}"
                     alt="Thumbnail {{ $loop->iteration }}"
                     class="w-full h-full object-cover">
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

      {{-- CTA WhatsApp --}}
      <a href="{{ $whatsappLink }}" target="_blank" rel="noopener"
         class="mt-5 w-full bg-green-600 hover:bg-yellow-500 text-white font-bold py-3 px-4 rounded-lg
                flex items-center justify-center text-base shadow-md transition">
        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24">
          <path d="M.057 24l1.687-6.163a11.94 11.94 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0c3.18 0 6.166 1.24 8.412 3.488A11.84 11.84 0 0123.94 12c-.003 6.557-5.338 11.892-11.894 11.892-1.99 0-3.952-.5-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.886-.001 2.269.655 4.357 1.849 6.081l-1.214 4.439 3.854-1.212z"/>
          <path d="M16.156 13.51c-.088-.146-.324-.234-.676-.41-.352-.176-2.083-1.027-2.406-1.144-.322-.117-.557-.176-.79.176-.234.352-.906 1.144-1.112 1.376-.205.234-.41.264-.762.088-.352-.176-1.486-.548-2.832-1.748-1.047-.934-1.753-2.086-1.96-2.438-.205-.352-.022-.54.154-.716.158-.158.352-.41.528-.616.176-.205.234-.352.352-.586.117-.234.059-.44-.03-.616-.088-.176-.79-1.91-1.082-2.608-.284-.683-.572-.589-.79-.6l-.676-.013c-.206 0-.54.078-.822.382-.282.303-1.08 1.055-1.08 2.573 0 1.518 1.106 2.984 1.26 3.19.158.205 2.176 3.325 5.273 4.66.737.318 1.312.508 1.76.65.74.235 1.414.202 1.948.123.594-.088 1.82-.742 2.078-1.46.258-.717.258-1.331.182-1.46z"/>
        </svg>
        Tanya & Nego via WA
      </a>
    </section>

  </div>
</div>
@endsection
