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

      {{-- Beli (scroll ke filter) --}}
        <a href="{{ route('landing.index') }}#filter-section"
        class="flex flex-col items-center justify-center text-white hover:text-red-500 text-xs font-medium w-full h-full"
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
         class="flex flex-col items-center justify-center text-white hover:text-red-500 text-xs font-medium w-full h-full" aria-label="Jual">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="mt-1">Jual</span>
      </a>

      {{-- WhatsApp --}}
<a href="{{ $whatsappLink }}" target="_blank" rel="noopener"
   class="flex flex-col items-center justify-center text-white hover:text-green-400 text-xs font-medium w-full h-full" aria-label="WhatsApp">
  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
    <path d="M12.04 2c-5.51 0-9.97 4.46-9.97 9.97 0 1.76.46 3.47 1.34 4.99L2 22l5.2-1.36a9.93 9.93 0 004.84 1.24h.01c5.51 0 9.97-4.46 9.97-9.97S17.55 2 12.04 2zm0 17.96h-.01c-1.52 0-3.01-.4-4.32-1.16l-.31-.18-3.09.81.83-3.01-.2-.31a7.9 7.9 0 01-1.23-4.3c0-4.36 3.55-7.91 7.91-7.91 2.11 0 4.09.82 5.58 2.31a7.87 7.87 0 012.33 5.59c0 4.36-3.55 7.91-7.91 7.91zm4.34-5.92c-.24-.12-1.43-.7-1.65-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.18a7.13 7.13 0 01-1.32-1.64c-.14-.24-.01-.37.11-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.29-.74-1.77-.2-.48-.4-.41-.54-.42l-.46-.01c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2 0 1.18.86 2.32.98 2.48.12.16 1.69 2.58 4.09 3.62.57.25 1.01.4 1.36.51.57.18 1.09.16 1.5.1.46-.07 1.43-.58 1.63-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28z"/>
  </svg>
  <span class="mt-1">WhatsApp</span>
</a>

    </div>
  </div>
</nav>
