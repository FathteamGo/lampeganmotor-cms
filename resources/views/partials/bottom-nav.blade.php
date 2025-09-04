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
         class="flex flex-col items-center justify-center text-white hover:text-red-500 text-xs font-medium w-full h-full" aria-label="WhatsApp">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
          <path d="M.057 24l1.687-6.163a11.94 11.94 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0c3.18 0 6.166 1.24 8.412 3.488A11.84 11.84 0 0123.94 12c-.003 6.557-5.338 11.892-11.894 11.892-1.99 0-3.952-.5-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.592 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892C6.602 1.999 2.167 6.433 2.165 11.885c0 2.27.655 4.357 1.849 6.081l-1.214 4.439 3.854-1.212z"/>
          <path d="M16.156 13.51c-.088-.146-.324-.234-.676-.41-.352-.176-2.083-1.027-2.406-1.144-.322-.117-.557-.176-.79.176-.234.352-.906 1.144-1.112 1.376-.205.234-.41.264-.762.088-.352-.176-1.486-.548-2.832-1.748-1.047-.934-1.753-2.086-1.96-2.438-.205-.352-.022-.54.154-.716.158-.158.352-.41.528-.616.176-.205.234-.352.352-.586.117-.234.059-.44-.03-.616-.088-.176-.79-1.91-1.082-2.608-.284-.683-.572-.589-.79-.6l-.676-.013c-.206 0-.54.078-.822.382-.282.303-1.08 1.055-1.08 2.573 0 1.518 1.106 2.984 1.26 3.19.158.205 2.176 3.325 5.273 4.66.737.318 1.312.508 1.76.65.74.235 1.414.202 1.948.123.594-.088 1.82-.742 2.078-1.46.258-.717.258-1.331.182-1.46z"/>
        </svg>
        <span class="mt-1">WhatsApp</span>
      </a>

    </div>
  </div>
</nav>
