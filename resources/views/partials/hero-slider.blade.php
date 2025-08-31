<div id="hero-slider" class="relative h-[50vh] md:h-[60vh] w-full overflow-hidden">
    @foreach ($heroSlides as $slide)
    <div class="hero-slide {{ $loop->first ? 'slide-active' : 'slide-inactive' }} absolute inset-0 transition-opacity duration-1000 ease-in-out">
        <img src="{{ $slide['imageUrl'] }}" alt="{{ $slide['title'] }}" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-opacity-50 flex items-center justify-center">
            <div class="text-center text-white p-4">
                <h1 class="text-3xl md:text-6xl font-extrabold mb-2 md:mb-4 drop-shadow-lg">{{ $slide['title'] }}</h1>
                <p class="text-md md:text-2xl drop-shadow-md">{{ $slide['subtitle'] }}</p>
            </div>
        </div>
    </div>
    @endforeach
    <button id="prev-slide" class="absolute top-1/2 left-2 md:left-4 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 p-2 md:p-3 rounded-full text-white transition">&lt;</button>
    <button id="next-slide" class="absolute top-1/2 right-2 md:right-4 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 p-2 md:p-3 rounded-full text-white transition">&gt;</button>
</div>