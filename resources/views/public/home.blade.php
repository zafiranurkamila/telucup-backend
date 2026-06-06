<x-layout-public>

    <!-- Header / Slider Section -->
    <section class="w-full relative bg-cover bg-center bg-no-repeat py-10" style="background-image: url('{{ asset('assets/home_original/bg_secondary.png') }}');">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
            <!-- Alpine.js Carousel -->
            <div x-data="{
                    activeSlide: 1,
                    slides: [
                        { id: 1, image: '{{ asset('assets/home_original/slider_1.jpg') }}' }
                    ],
                    next() { this.activeSlide = this.activeSlide === this.slides.length ? 1 : this.activeSlide + 1 },
                    prev() { this.activeSlide = this.activeSlide === 1 ? this.slides.length : this.activeSlide - 1 },
                    init() { setInterval(() => { this.next() }, 5000) }
                }" 
                class="relative w-full overflow-hidden rounded-lg shadow-lg border-4 border-white">
                
                <template x-for="slide in slides" :key="slide.id">
                    <div x-show="activeSlide === slide.id" 
                         x-transition:enter="transition ease-out duration-500" 
                         x-transition:enter-start="opacity-0 transform scale-95" 
                         x-transition:enter-end="opacity-100 transform scale-100" 
                         class="w-full">
                        <img :src="slide.image" class="w-full h-auto object-cover" alt="Slider Image">
                    </div>
                </template>
                
            </div>
        </div>
    </section>

    <!-- Potret Tel-U Cup Section -->
    <section class="w-full relative bg-cover bg-center bg-no-repeat py-16 bg-gray-50" style="background-image: url('{{ asset('assets/home_original/bg_primary.png') }}');">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-[#b6252a] mb-8 bg-white/80 inline-block px-6 py-2 rounded-full shadow-sm">POTRET TEL-U CUP</h2>
            
            <!-- Alpine.js Carousel for Potret -->
            <div x-data="{
                    activeSlide: 1,
                    slides: [
                        { id: 1, image: '{{ asset('assets/home_original/foto_1.jpg') }}' },
                        { id: 2, image: '{{ asset('assets/home_original/foto_2.jpg') }}' },
                        { id: 3, image: '{{ asset('assets/home_original/foto_3.jpg') }}' },
                        { id: 4, image: '{{ asset('assets/home_original/foto_4.jpg') }}' },
                        { id: 5, image: '{{ asset('assets/home_original/foto_5.jpg') }}' }
                    ],
                    next() { this.activeSlide = this.activeSlide === this.slides.length ? 1 : this.activeSlide + 1 },
                    prev() { this.activeSlide = this.activeSlide === 1 ? this.slides.length : this.activeSlide - 1 },
                    init() { setInterval(() => { this.next() }, 3000) }
                }" 
                class="relative w-full overflow-hidden rounded-lg shadow-xl mx-auto md:w-3/4 lg:w-2/3 border-4 border-white">
                
                <template x-for="slide in slides" :key="slide.id">
                    <div x-show="activeSlide === slide.id" 
                         x-transition:enter="transition ease-in-out duration-500" 
                         x-transition:enter-start="opacity-0 translate-x-full" 
                         x-transition:enter-end="opacity-100 translate-x-0" 
                         class="w-full">
                        <img :src="slide.image" class="w-full h-auto object-cover" alt="Potret Image">
                    </div>
                </template>

                <!-- Controls -->
                <button @click="prev()" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-30 text-white p-3 hover:bg-opacity-50 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button @click="next()" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-30 text-white p-3 hover:bg-opacity-50 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
                
                <!-- Indicators -->
                <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
                    <template x-for="slide in slides" :key="slide.id">
                        <button @click="activeSlide = slide.id" :class="{'bg-[#b6252a]': activeSlide === slide.id, 'bg-gray-300': activeSlide !== slide.id}" class="w-3 h-3 rounded-full focus:outline-none shadow-sm"></button>
                    </template>
                </div>
            </div>
        </div>
    </section>

    <!-- Daftar Kompetisi Section -->
    <section class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center text-[#b6252a] mb-12">DAFTAR KOMPETISI TEL-U CUP 2025</h2>
            
            <h3 class="text-2xl font-semibold mb-6 text-gray-800 border-b-2 border-gray-200 pb-2">OLAHRAGA</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-16">
                @foreach(['Basket', 'Tenis Lapangan', 'Bulutangkis', 'Voli', 'Futsal', 'Tenis Meja', 'E-Sport', 'Catur', 'Lari'] as $olahraga)
                <div class="bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md hover:-translate-y-1 transition-all p-6 flex flex-col items-center justify-center h-48 group cursor-default">
                    <img src="{{ asset('assets/home_original/logo_' . $olahraga . '.png') }}" class="w-3/4 object-contain group-hover:scale-105 transition-transform" alt="{{ $olahraga }}">
                </div>
                @endforeach
            </div>

            <h3 class="text-2xl font-semibold mb-6 text-gray-800 border-b-2 border-gray-200 pb-2">SENI & HIBURAN</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach(['Senam Kreasi', 'Lomba Masak', 'Tel-U Idol', 'Sitkom Sketsa', 'Kaulinan Barudak', 'Cerdas Cermat', 'Stand Up Comedy', 'Fun Quiz', 'Berpacu Dalam Melodi'] as $seni)
                <div class="bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md hover:-translate-y-1 transition-all p-6 flex flex-col items-center justify-center h-48 group cursor-default">
                    <img src="{{ asset('assets/home_original/logo_' . $seni . '.png') }}" class="w-3/4 object-contain group-hover:scale-105 transition-transform" alt="{{ $seni }}">
                </div>
                @endforeach
            </div>
        </div>
    </section>

</x-layout-public>