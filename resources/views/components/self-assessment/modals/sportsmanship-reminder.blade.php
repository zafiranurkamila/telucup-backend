<div x-show="showReminderModal" 
     style="display: none;" 
     class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 backdrop-blur-sm px-4"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl transition-all">
        <!-- Header -->
        <div class="bg-[#B41F2A] p-6 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-white/20 backdrop-blur-md">
                <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-white">Pesan Sportivitas</h2>
            <p class="mt-2 text-sm text-white/80">Mari junjung tinggi nilai-nilai sportivitas dalam setiap pertandingan.</p>
        </div>

        <!-- Carousel Content -->
        <div class="relative bg-gray-50 p-6" x-show="posters.length > 0">
            <template x-for="(poster, index) in posters" :key="poster.id">
                <div x-show="currentPosterIndex === index" class="text-center transition-all">
                    <div class="relative mx-auto mb-6 overflow-hidden rounded-xl bg-gray-200 shadow-md h-64 w-full">
                        <img :src="poster.image_url" :alt="poster.title" class="h-full w-full object-cover" />
                    </div>
                    <h3 class="mb-2 text-lg font-bold text-gray-900" x-text="poster.title"></h3>
                    <p class="text-sm text-gray-600" x-text="poster.description"></p>
                </div>
            </template>
            
            <!-- Indicators -->
            <div class="mt-6 flex justify-center gap-2" x-show="posters.length > 1">
                <template x-for="(_, index) in posters" :key="index">
                    <button @click="currentPosterIndex = index"
                            class="h-2 w-2 rounded-full transition-all"
                            :class="currentPosterIndex === index ? 'bg-[#B41F2A] w-4' : 'bg-gray-300 hover:bg-gray-400'"></button>
                </template>
            </div>
            
            <!-- Navigation -->
            <button type="button" @click="prevPoster()" 
                    x-show="currentPosterIndex > 0"
                    class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 text-gray-800 shadow-md hover:bg-white hover:text-[#B41F2A] transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <button type="button" @click="nextPoster()" 
                    x-show="currentPosterIndex < posters.length - 1"
                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 text-gray-800 shadow-md hover:bg-white hover:text-[#B41F2A] transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>

        <!-- Actions -->
        <div class="border-t border-gray-100 bg-white p-6">
            <button type="button" @click="goToResultPage()" 
                    :disabled="!hasReachedEnd"
                    class="w-full rounded-xl px-6 py-3.5 text-sm font-bold shadow-md transition-all flex items-center justify-center gap-2"
                    :class="hasReachedEnd ? 'bg-[#B41F2A] text-white hover:bg-[#8A1520] hover:shadow-lg' : 'bg-gray-200 text-gray-400 cursor-not-allowed'">
                <span>Lanjutkan ke Hasil</span>
                <svg x-show="hasReachedEnd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </button>
            <p x-show="!hasReachedEnd" class="mt-3 text-center text-xs text-gray-500">
                Silakan lihat semua poster pesan sportivitas untuk melanjutkan.
            </p>
        </div>
    </div>
</div>
