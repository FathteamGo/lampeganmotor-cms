@php
    // Bisa ambil link WA default atau kosong, modal nanti ambil dari model
    $whatsappLink = '#';
@endphp

{{-- NAVIGASI BAWAH (MOBILE-FIRST) --}}
<nav class="fixed bottom-0 left-0 right-0 z-50">
    <div class="mx-auto max-w-sm bg-black border-t border-black shadow-lg">
        <div class="flex justify-around items-center h-16">

            {{-- Beranda --}}
            <a href="{{ route('landing.index') }}"
               class="flex flex-col items-center justify-center text-white hover:text-red-500 text-xs font-medium w-full h-full" aria-label="Beranda">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="mt-1">Beranda</span>
            </a>

            {{-- Beli --}}
            <a href="{{ route('landing.index') }}#filter-section"
               class="flex flex-col items-center justify-center text-white hover:text-red-500 pe-3 text-xs font-medium w-full h-full"
               aria-label="Beli">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span class="mt-1">Beli</span>
            </a>

            {{-- Jual --}}
            <a href="{{ route('landing.sell.form') }}"
               class="flex flex-col items-center justify-center pe-5 text-white hover:text-red-500 text-xs font-medium w-full h-full" aria-label="Jual">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="mt-1">Jual</span>
            </a>

            {{-- WhatsApp (Modal) --}}
            @include('partials.whatsapp-modal')

        </div>
    </div>
</nav>
