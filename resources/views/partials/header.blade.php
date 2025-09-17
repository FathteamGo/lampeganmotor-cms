<header class="bg-white/80 backdrop-blur-sm text-gray-900 sticky top-0 z-50 shadow-lg">
  <div class="mx-auto max-w-5xl px-4">
    <div class="flex items-center justify-between h-16">

      {{-- Logo kiri --}}
      <img 
          src="{{ $header->logo ? Storage::url($header->logo) : asset('Images/logo/lampeganmtrbdg.jpg') }}" 
          alt="{{ $header->site_name ?? 'Lampegan Motor' }}" 
          class="h-10 w-auto object-contain rounded-lg mr-2"
          onerror="this.onerror=null;this.src='{{ asset('Images/logo/lampeganmtrbdg.jpg') }}';"
      />



      {{-- Judul tengah --}}
      <a href="{{ route('landing.index') }}" class="text-lg md:text-xl font-extrabold tracking-wide text-gray-900 text-center flex-1">
        {{ $header->site_name ?? 'Lampegan Motor' }}
      </a>

      {{-- Sosial kanan --}}
      <div class="flex items-center gap-4">
        @if($header->instagram_url ?? false)
          <a href="{{ $header->instagram_url }}" target="_blank" rel="noopener"
             class="text-gray-700 hover:text-black transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="h-6 w-6">
              <path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2zm0 1.5A4.25 4.25 0 0 0 3.5 7.75v8.5A4.25 4.25 0 0 0 7.75 20.5h8.5a4.25 4.25 0 0 0 4.25-4.25v-8.5A4.25 4.25 0 0 0 16.25 3.5h-8.5zm4.25 4a5.25 5.25 0 1 1 0 10.5 5.25 5.25 0 0 1 0-10.5zm0 1.5a3.75 3.75 0 1 0 0 7.5 3.75 3.75 0 0 0 0-7.5zm5.25-.75a.75.75 0 1 1 0 1.5.75.75 0 0 1 0-1.5z"/>
            </svg>
          </a>
        @endif

        @if($header->tiktok_url ?? false)
          <a href="{{ $header->tiktok_url }}" target="_blank" rel="noopener"
             class="text-gray-700 hover:text-black transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 48 48" fill="currentColor">
              <path d="M30 4c2.2 3.5 5.6 6 9.6 6.8V17c-3.7-.1-7.2-1.4-9.6-3.6V30c0 6.6-5.4 12-12 12S6 36.6 6 30s5.4-12 12-12c1.3 0 2.6.2 3.8.7V24c-.9-.4-1.9-.6-2.9-.6-3.9 0-7 3.1-7 6.9s3.1 6.9 7 6.9 7-3.1 7-6.9V4h4z"/>
            </svg>
          </a>
        @endif
      </div>
    </div>
  </div>
</header>
