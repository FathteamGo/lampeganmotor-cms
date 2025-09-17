@php
    $admins = \App\Models\WhatsAppNumber::where('is_active', true)->get();
@endphp

<div x-data="{ open: false }">
    <!-- Tombol WA -->
    <button @click="open = true" 
            class="flex flex-col items-center justify-center text-white hover:text-green-400 pe-4 text-xs font-medium w-full h-full" 
            aria-label="WhatsApp">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" class="w-5 h-5 mb-1 fill-current">
            <path d="M128 0C57.31 0 0 56.57 0 126.46c0 22.32 5.93 43.32 16.23 61.51L0 256l70.25-22.81c17.94 9.82 38.49 15.42 60.4 15.42 70.69 0 128-56.57 128-126.46S198.69 0 128 0zm0 230.77c-19.33 0-37.3-5.42-52.53-14.79l-3.74-2.27-41.68 13.53 13.55-40.2-2.44-3.91C33.01 168.38 26.46 147.84 26.46 126.46 26.46 69.84 72.55 23.73 128 23.73s101.54 46.11 101.54 102.73S183.45 230.77 128 230.77zM187.63 150.3c-3.19-1.59-18.88-9.27-21.81-10.34-2.93-1.07-5.07-1.59-7.2 1.6-2.13 3.19-8.27 10.34-10.14 12.47-1.87 2.13-3.73 2.4-6.92.8-3.19-1.59-13.46-4.97-25.64-15.83-9.47-8.41-15.87-18.79-17.74-21.98-1.87-3.19-.2-4.92 1.4-6.52 1.43-1.43 3.19-3.73 4.79-5.59 1.6-1.86 2.13-3.19 3.19-5.32 1.07-2.13.53-3.99-.27-5.59-.8-1.59-7.2-17.36-9.87-23.74-2.6-6.24-5.25-5.39-7.2-5.48l-6.16-.11c-2.13 0-5.59.8-8.52 3.99-2.93 3.19-11.2 10.94-11.2 26.69 0 15.74 11.46 30.95 13.06 33.08 1.6 2.13 22.51 34.38 54.53 48.16 7.62 3.29 13.55 5.25 18.19 6.72 7.63 2.42 14.57 2.08 20.07 1.26 6.12-.91 18.88-7.72 21.53-15.18 2.67-7.46 2.67-13.85 1.87-15.18-.8-1.33-2.93-2.13-6.12-3.72z"/>
        </svg>
        <span class="text-xs">WhatsApp</span>
    </button>

    <!-- Modal WA muncul hanya saat klik -->
    <template x-if="open">
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg w-80 p-6 relative">
                <h3 class="text-lg font-bold mb-4">Hubungi Admin</h3>
                <ul class="space-y-2">
                    @foreach($admins as $admin)
                        <li>
                            <a href="https://wa.me/{{ $admin->number }}" target="_blank"
                               class="flex items-center gap-2 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                {{ $admin->name }} ({{ $admin->number }})
                            </a>
                        </li>
                    @endforeach
                </ul>
                <button @click="open = false" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">&times;</button>
            </div>
        </div>
    </template>
</div>
