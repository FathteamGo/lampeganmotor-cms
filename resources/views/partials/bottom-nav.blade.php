 {{-- NAVIGASI BAWAH (MOBILE-FIRST) --}}
 <nav class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-lg z-50 block">
     <div class="flex justify-around items-center h-16">

         <a href="{{ route('landing.index') }}" class="flex flex-col items-center justify-center text-gray-600 hover:text-red-600 dark:text-gray-300 dark:hover:text-red-500 text-xs font-medium w-full h-full">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                 <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
             </svg>
             <span class="mt-1">Beranda</span>
         </a>

         <a href="#filter-section" class="flex flex-col items-center justify-center text-gray-600 hover:text-red-600 dark:text-gray-300 dark:hover:text-red-500 text-xs font-medium w-full h-full">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                 <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
             </svg>
             <span class="mt-1">Beli</span>
         </a>

         <a href="{{ route('landing.sell.form') }}" class="flex flex-col items-center justify-center text-gray-600 hover:text-red-600 dark:text-gray-300 dark:hover:text-red-500 text-xs font-medium w-full h-full">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                 <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
             </svg>
             <span class="mt-1">Jual</span>
         </a>

         <a href="{{ $whatsappLink }}" target="_blank" class="flex flex-col items-center justify-center text-gray-600 hover:text-red-600 dark:text-gray-300 dark:hover:text-red-500 text-xs font-medium w-full h-full">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                 <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.894 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.886-.001 2.269.655 4.357 1.849 6.081l-1.214 4.439 4.572-1.21zM9.06 8.928c-.09-.158-.297-.25-.504-.25h-.17c-.183 0-.363.044-.519.138-.158.095-.319.228-.445.404-.126.176-.181.375-.181.574s.054.398.181.573c.126.176.287.309.445.403.156.095.336.139.519.139h.17c.207 0 .414-.092.504-.25.09-.158.138-.354.138-.549s-.048-.391-.138-.549zM11.191 11.449c-.191.333-.42.613-.69.828-.27.215-.578.348-.901.39-.323.043-.654-.006-.957-.108s-.58-.25-.799-.438c-.219-.188-.411-.411-.57-.66-.16-.249-.288-.522-.379-.811-.091-.289-.137-.591-.137-.899s.045-.609.137-.899c.091-.289.219-.562.379-.81.16-.249.351-.472.57-.66.219-.188.481-.343.799-.437.303-.102.634-.151.957-.108.323.043.631.175.901.39.27.215.5.495.69.828.191.334.287.702.287 1.071s-.096.737-.287 1.071zM15.463 8.928c-.09-.158-.297-.25-.504-.25h-.17c-.183 0-.363.044-.519.138-.158.095-.319.228-.445.404-.126.176-.181.375-.181.574s.054.398.181.573c.126.176.287.309.445.403.156.095.336.139.519.139h.17c.207 0 .414-.092.504-.25.09-.158.138-.354.138-.549s-.048-.391-.138-.549z" />
             </svg>
             <span class="mt-1">Chat</span>
         </a>
     </div>
 </nav>