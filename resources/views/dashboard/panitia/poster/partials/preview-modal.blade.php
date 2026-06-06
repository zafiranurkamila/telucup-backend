<div class="fixed inset-0 bg-gray-900/90 z-50 flex items-center justify-center p-4 backdrop-blur-sm" x-show="isPreviewOpen" x-transition.opacity style="display: none;">
    <div class="relative w-full max-w-lg flex flex-col items-center" @click.away="isPreviewOpen = false">
        
        <div class="absolute -top-12 right-0 md:-right-12">
            <button @click="isPreviewOpen = false" class="text-white hover:text-red-400 p-2 rounded-full transition-colors bg-black/50 hover:bg-black/70">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <template x-if="selectedPoster">
            <div class="w-full">
                <div class="rounded-2xl overflow-hidden shadow-2xl relative">
                    <img :src="selectedPoster.image_url" :alt="selectedPoster.title" class="w-full max-h-[80vh] object-contain bg-black">
                </div>
                
                <div class="mt-6 text-center text-white">
                    <h3 class="text-xl font-bold mb-2" x-text="selectedPoster.title"></h3>
                    <p class="text-sm text-gray-300 max-w-md mx-auto" x-text="selectedPoster.description"></p>
                </div>
            </div>
        </template>
        
    </div>
</div>
