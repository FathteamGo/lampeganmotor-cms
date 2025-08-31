<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light"> {{-- [OK] Menggunakan helper Laravel untuk bahasa --}}

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Lampegan Motor - Jual Beli Berkualitas')</title>

    {{-- [BAIK] Logika Vite/CDN sudah benar --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- [INFO] Font dan style inline sudah OK untuk pendekatan ini --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .slide-active {
            display: block;
        }

        .slide-inactive {
            display: none;
        }

        html,
        body {
            height: 100%;
            margin: 0;
        }

        #app {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex: 1;
        }

        html {
            scroll-behavior: smooth;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-50  font-sans" id="app">

    @include('partials.header')

    <main id="main-content">
        @yield('content')
    </main>

    @include('partials.footer')

    @php
    // Variabel ini sekarang mengambil nomor baru dari config/contact.php
    $whatsappNumber = config('contact.whatsapp');
    $whatsappMessage = urlencode("Halo, saya ingin bertanya tentang motor di Lampegan Motor.");
    $whatsappLink = "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}";
    @endphp

    {{-- [BAIK] Mengirim variabel ke partial sudah benar --}}
    @include('partials.bottom-nav', ['whatsappLink' => $whatsappLink])

    @stack('scripts')

    {{-- [DIPERBAIKI] Skrip JavaScript dipindahkan ke file terpisah agar lebih rapi --}}
    {{-- Anda bisa memindahkan seluruh isi <script> di bawah ini ke dalam file resources/js/app.js --}}
    <script>
        // Theme Toggle Script
        const themeToggleBtn = document.getElementById('theme-toggle');
        const htmlElement = document.documentElement;

        if (themeToggleBtn) { // [DIPERBAIKI] Tambahkan pengecekan jika tombol tidak ada
            const applyTheme = (theme) => {
                if (theme === 'dark') {
                    htmlElement.classList.add('dark');
                    themeToggleBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" /></svg>`;
                } else {
                    htmlElement.classList.remove('dark');
                    themeToggleBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.459 4.292a1 1 0 01-1.414 0l-1.414-1.414a1 1 0 010-1.414l1.414-1.414a1 1 0 011.414 0l1.414 1.414a1 1 0 010 1.414l-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-1.413 1.415a1 1 0 11-1.414-1.414l1.414-1.414a1 1 0 011.414 0zM10 15a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm-4 0a1 1 0 01-1 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm-.536-1.879a1 1 0 010 1.415l-1.414 1.413a1 1 0 01-1.414-1.414l1.414-1.414a1 1 0 011.414 0zm4-4.505a1 1 0 011-1h1a1 1 0 110 2h-1a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>`;
                }
            };

            themeToggleBtn.addEventListener('click', () => {
                const newTheme = htmlElement.classList.contains('dark') ? 'light' : 'dark';
                localStorage.setItem('theme', newTheme);
                applyTheme(newTheme);
            });

            document.addEventListener('DOMContentLoaded', () => {
                const savedTheme = localStorage.getItem('theme') || 'light';
                applyTheme(savedTheme);
            });
        }
    </script>
</body>

</html>