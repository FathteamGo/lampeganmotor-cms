<header class="bg-white/80 backdrop-blur-sm text-gray-900 sticky top-0 z-50 shadow-lg">
  <script defer src="https://analytics.xpc.my.id/script.js" data-website-id="1bb5db62-177b-40a3-875c-be0817c5f93a"></script>
    <div class="w-full px-4 md:px-8">
    <div class="flex items-center justify-between h-16">

      {{-- Kiri: Logo + (Desktop Title) --}}
      <div class="flex items-center">
          <img
            src="{{ $header->logo ? Storage::url($header->logo) : asset('Images/logo/lampeganmtrbdg.jpg') }}"
            alt="{{ $header->site_name ?? 'Lampegan Motor' }}"
            class="h-10 w-auto object-contain rounded-lg mr-2"
            onerror="this.onerror=null;this.src='{{ asset('Images/logo/lampeganmtrbdg.jpg') }}';"
          />
          {{-- Judul Desktop (Hidden on Mobile) --}}
          <a href="{{ route('landing.index') }}" 
             class="hidden md:block text-xl font-extrabold tracking-wide text-gray-900 ml-1">
            {{ $header->site_name ?? 'Lampegan Motor' }}
          </a>
      </div>

      {{-- Tengah: Judul Mobile (Hidden on Desktop) --}}
      <a
        href="{{ route('landing.index') }}"
        class="md:hidden flex-1 text-center text-lg font-extrabold tracking-wide text-gray-900 -ml-2"
      >
        {{ $header->site_name ?? 'Lampegan Motor' }}
      </a>

      {{-- Desktop Navigation (Hidden on Mobile) --}}
      <nav class="hidden md:flex items-center gap-8 mx-auto">
          <a href="{{ route('landing.index') }}" class="text-sm font-bold text-gray-700 hover:text-red-600 uppercase tracking-wider transition">Beranda</a>
          <a href="{{ route('landing.index') }}#filter-section" class="text-sm font-bold text-gray-700 hover:text-red-600 uppercase tracking-wider transition">Beli</a>
          <a href="{{ route('landing.sell.form') }}" class="text-sm font-bold text-gray-700 hover:text-red-600 uppercase tracking-wider transition">Jual</a>
      </nav>

      {{-- Kanan: Sosial Media + WhatsApp Desktop --}}
      <div class="flex items-center gap-4">
        {{-- Instagram --}}
        @if($header->instagram_url ?? false)
          <a
            href="{{ $header->instagram_url }}"
            target="_blank"
            rel="noopener"
            class="text-gray-700 hover:text-black transition"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="h-6 w-6">
              <path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2zm0 1.5A4.25 4.25 0 0 0 3.5 7.75v8.5A4.25 4.25 0 0 0 7.75 20.5h8.5a4.25 4.25 0 0 0 4.25-4.25v-8.5A4.25 4.25 0 0 0 16.25 3.5h-8.5zm4.25 4a5.25 5.25 0 1 1 0 10.5 5.25 5.25 0 0 1 0-10.5zm0 1.5a3.75 3.75 0 1 0 0 7.5 3.75 3.75 0 0 0 0-7.5zm5.25-.75a.75.75 0 1 1 0 1.5.75.75 0 0 1 0-1.5z"/>
            </svg>
          </a>
        @endif

        {{-- TikTok --}}
        @if($header->tiktok_url ?? false)
          <a
            href="{{ $header->tiktok_url }}"
            target="_blank"
            rel="noopener"
            class="text-gray-700 hover:text-black transition"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" fill="currentColor" class="h-6 w-6">
              <path d="M30 4c2.2 3.5 5.6 6 9.6 6.8V17c-3.7-.1-7.2-1.4-9.6-3.6V30c0 6.6-5.4 12-12 12S6 36.6 6 30s5.4-12 12-12c1.3 0 2.6.2 3.8.7V24c-.9-.4-1.9-.6-2.9-.6-3.9 0-7 3.1-7 6.9s3.1 6.9 7 6.9 7-3.1 7-6.9V4h4z"/>
            </svg>
          </a>
        @endif

        {{-- WhatsApp Desktop (Hidden on Mobile) --}}
        <div class="hidden md:block relative" x-data="{ open: false }">
            <button @click="open = !open" 
                    class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-bold transition shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current" viewBox="0 0 256 256">
                    <path d="M128 0C57.31 0 0 56.57 0 126.46c0 22.32 5.93 43.32 16.23 61.51L0 256l70.25-22.81c17.94 9.82 38.49 15.42 60.4 15.42 70.69 0 128-56.57 128-126.46S198.69 0 128 0zm0 230.77c-19.33 0-37.3-5.42-52.53-14.79l-3.74-2.27-41.68 13.53 13.55-40.2-2.44-3.91C33.01 168.38 26.46 147.84 26.46 126.46 26.46 69.84 72.55 23.73 128 23.73s101.54 46.11 101.54 102.73S183.45 230.77 128 230.77zM187.63 150.3c-3.19-1.59-18.88-9.27-21.81-10.34-2.93-1.07-5.07-1.59-7.2 1.6-2.13 3.19-8.27 10.34-10.14 12.47-1.87 2.13-3.73 2.4-6.92.8-3.19-1.59-13.46-4.97-25.64-15.83-9.47-8.41-15.87-18.79-17.74-21.98-1.87-3.19-.2-4.92 1.4-6.52 1.43-1.43 3.19-3.73 4.79-5.59 1.6-1.86 2.13-3.19 3.19-5.32 1.07-2.13.53-3.99-.27-5.59-.8-1.59-7.2-17.36-9.87-23.74-2.6-6.24-5.25-5.39-7.2-5.48l-6.16-.11c-2.13 0-5.59.8-8.52 3.99-2.93 3.19-11.2 10.94-11.2 26.69 0 15.74 11.46 30.95 13.06 33.08 1.6 2.13 22.51 34.38 54.53 48.16 7.62 3.29 13.55 5.25 18.19 6.72 7.63 2.42 14.57 2.08 20.07 1.26 6.12-.91 18.88-7.72 21.53-15.18 2.67-7.46 2.67-13.85 1.87-15.18-.8-1.33-2.93-2.13-6.12-3.72z"/>
                </svg>
                WhatsApp
            </button>
            <div x-show="open" @click.outside="open = false" 
                 class="absolute right-0 mt-3 w-72 bg-white rounded-lg shadow-xl z-50 p-4 border border-gray-100"
                 style="display: none;"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100">
                <h3 class="text-gray-900 font-bold mb-3 text-sm border-b pb-2">Hubungi Admin</h3>
                @php
                    $hcontacts = \App\Models\WhatsAppNumber::where('is_active', true)
                        ->whereHas('user', fn ($q) => $q->where('role', 'owner')->orWhere('role', 'admin'))
                        ->get();
                @endphp
                 <div class="space-y-2 max-h-60 overflow-y-auto">
                    @foreach($hcontacts as $contact)
                         <a href="https://wa.me/{{ $contact->number }}" target="_blank"
                           class="flex items-center gap-3 w-full bg-slate-50 hover:bg-green-50 p-2 rounded-lg transition text-gray-800 text-sm border border-transparent hover:border-green-200 group">
                             <span class="bg-green-500 text-white p-1.5 rounded-full group-hover:scale-110 transition">
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 256 256"><path d="M128 0C57.31 0 0 56.57 0 126.46c0 22.32 5.93 43.32 16.23 61.51L0 256l70.25-22.81c17.94 9.82 38.49 15.42 60.4 15.42 70.69 0 128-56.57 128-126.46S198.69 0 128 0zm0 230.77c-19.33 0-37.3-5.42-52.53-14.79l-3.74-2.27-41.68 13.53 13.55-40.2-2.44-3.91C33.01 168.38 26.46 147.84 26.46 126.46 26.46 69.84 72.55 23.73 128 23.73s101.54 46.11 101.54 102.73S183.45 230.77 128 230.77zM187.63 150.3c-3.19-1.59-18.88-9.27-21.81-10.34-2.93-1.07-5.07-1.59-7.2 1.6-2.13 3.19-8.27 10.34-10.14 12.47-1.87 2.13-3.73 2.4-6.92.8-3.19-1.59-13.46-4.97-25.64-15.83-9.47-8.41-15.87-18.79-17.74-21.98-1.87-3.19-.2-4.92 1.4-6.52 1.43-1.43 3.19-3.73 4.79-5.59 1.6-1.86 2.13-3.19 3.19-5.32 1.07-2.13.53-3.99-.27-5.59-.8-1.59-7.2-17.36-9.87-23.74-2.6-6.24-5.25-5.39-7.2-5.48l-6.16-.11c-2.13 0-5.59.8-8.52 3.99-2.93 3.19-11.2 10.94-11.2 26.69 0 15.74 11.46 30.95 13.06 33.08 1.6 2.13 22.51 34.38 54.53 48.16 7.62 3.29 13.55 5.25 18.19 6.72 7.63 2.42 14.57 2.08 20.07 1.26 6.12-.91 18.88-7.72 21.53-15.18 2.67-7.46 2.67-13.85 1.87-15.18-.8-1.33-2.93-2.13-6.12-3.72z"/></svg>
                             </span>
                             {{ $contact->name }}
                         </a>
                    @endforeach
                 </div>
            </div>
        </div>
      </div>

    </div>
  </div>
</header>
