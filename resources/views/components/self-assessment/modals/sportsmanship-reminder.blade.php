<div x-show="showReminderModal" 
     style="display: none;" 
     class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-slate-950/85 px-4 py-6 backdrop-blur-sm"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    <div class="relative my-auto w-full max-w-3xl overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-white/10 transition-all">
        <!-- Header -->
        <div class="border-b border-gray-100 bg-white px-5 py-4 sm:px-6">
            <div class="flex items-center gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-[#B41F2A] text-white shadow-sm shadow-red-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#B41F2A]">Pesan Sportivitas</p>
                    <h2 class="text-lg font-black leading-tight text-gray-950 sm:text-xl">Jaga fair play sampai pertandingan selesai</h2>
                    <p class="mt-1 text-sm leading-relaxed text-gray-500">Baca pesan berikut sebelum melihat hasil self-assessment.</p>
                </div>
            </div>
        </div>

        <!-- Carousel Content -->
        <div class="relative bg-gray-50 px-4 py-5 sm:px-6 sm:py-6" x-show="posters.length > 0">
            <template x-for="(poster, index) in posters" :key="poster.id">
                <div x-show="currentPosterIndex === index" class="grid gap-5 transition-all md:grid-cols-[minmax(0,360px)_1fr] md:items-center">
                    <div class="relative mx-auto aspect-[2/3] h-[58vh] max-h-[600px] w-full max-w-[400px] overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg shadow-gray-200/70">
                        <img :src="poster.image_url" :alt="poster.title" class="relative h-full w-full object-contain" />
                    </div>
                    <div class="text-center md:text-left">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400" x-text="`${currentPosterIndex + 1} dari ${posters.length}`"></p>
                        <h3 class="mt-2 text-xl font-black leading-tight text-gray-950 sm:text-2xl" x-text="poster.title"></h3>
                        <p class="mt-3 text-sm leading-relaxed text-gray-600" x-text="poster.description || 'Mari junjung tinggi nilai-nilai sportivitas dalam setiap pertandingan.'"></p>
                    </div>
                </div>
            </template>
            
            <!-- Indicators -->
            <div class="mt-5 flex justify-center gap-2" x-show="posters.length > 1">
                <template x-for="(_, index) in posters" :key="index">
                    <button @click="currentPosterIndex = index"
                            type="button"
                            class="h-2 rounded-full transition-all"
                            :class="currentPosterIndex === index ? 'w-6 bg-[#B41F2A]' : 'w-2 bg-gray-300 hover:bg-gray-400'"></button>
                </template>
            </div>
            
            <!-- Navigation -->
            <button type="button" @click="prevPoster()" 
                    x-show="currentPosterIndex > 0"
                    class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-white/95 p-2 text-gray-800 shadow-md ring-1 ring-gray-200 transition-colors hover:bg-white hover:text-[#B41F2A]">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <button type="button" @click="nextPoster()" 
                    x-show="currentPosterIndex < posters.length - 1"
                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-white/95 p-2 text-gray-800 shadow-md ring-1 ring-gray-200 transition-colors hover:bg-white hover:text-[#B41F2A]">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
        </div>

        <!-- Actions -->
        <div class="border-t border-gray-100 bg-white px-5 py-4 sm:px-6">
            <button type="button" @click="goToResultPage()" 
                    :disabled="!hasReachedEnd"
                    class="flex w-full items-center justify-center gap-2 rounded-lg px-6 py-3.5 text-sm font-bold shadow-sm transition-all"
                    :class="hasReachedEnd ? 'bg-[#B41F2A] text-white hover:bg-[#8A1520] hover:shadow-md' : 'cursor-not-allowed bg-gray-200 text-gray-400'">
                <span>Lanjutkan ke Hasil</span>
                <svg x-show="hasReachedEnd" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </button>
            <p x-show="!hasReachedEnd" class="mt-3 text-center text-xs text-gray-500">
                Silakan lihat semua poster pesan sportivitas untuk melanjutkan.
            </p>
        </div>
    </div>
</div>
