<!-- Video Section (konsisten max-w-sm) -->
<section class="bg-white py-8">
  <div class="mx-auto max-w-sm px-4">
    <h2 class="text-2xl font-extrabold text-center text-black mb-6 inline-block border-b-4 border-yellow-400">
        Video Review
    </h2>

    <div class="w-full bg-white dark:bg-black rounded-lg shadow-xl overflow-hidden">
      <div class="relative" style="padding-top:56.25%;">
        @if ($video && $video->youtube_url)
          @php
            // ambil ID dari link YouTube, support format watch?v=xxx atau share link
            preg_match(
              '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i',
              $video->youtube_url,
              $matches
            );
            $videoId = $matches[1] ?? null;
          @endphp

          @if ($videoId)
            <iframe
              class="absolute top-0 left-0 w-full h-full"
              src="https://www.youtube.com/embed/{{ $videoId }}"
              title="YouTube video player"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowfullscreen
              loading="lazy"
              referrerpolicy="strict-origin-when-cross-origin"
            ></iframe>
          @else
            <div class="flex items-center justify-center h-full text-gray-500">
              URL YouTube tidak valid
            </div>
          @endif
        @else
          <div class="flex items-center justify-center h-full text-gray-500">
            Belum ada video
          </div>
        @endif
      </div>
    </div>
  </div>
</section>
