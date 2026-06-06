<div class="fixed inset-0 bg-gray-900/95 z-50 flex items-center justify-center p-4 backdrop-blur-sm" x-show="photoToPreview !== null" x-transition.opacity style="display: none;">
    <div class="relative w-full h-full flex flex-col items-center justify-center" @click.self="photoToPreview = null">
        
        <button @click="photoToPreview = null" class="absolute top-4 right-4 text-white hover:text-red-400 p-2 bg-black/50 hover:bg-black/80 rounded-full transition-colors z-10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <template x-if="photoToPreview">
            <div class="flex flex-col items-center justify-center max-h-full w-full">
                <img :src="photoToPreview.image_url" class="max-w-full max-h-[85vh] object-contain shadow-2xl rounded-sm bg-black" @click.stop>
                
                <div class="mt-4 text-center text-white bg-black/50 px-4 py-2 rounded-lg backdrop-blur-sm" @click.stop>
                    <p class="text-sm font-medium" x-text="'Diunggah oleh: ' + (photoToPreview.uploader ? photoToPreview.uploader.name : ('ID ' + photoToPreview.uploaded_by))"></p>
                    <p class="text-xs text-gray-300 mt-1" x-text="new Date(photoToPreview.created_at).toLocaleString('id-ID', { dateStyle: 'long', timeStyle: 'short' })"></p>
                </div>
            </div>
        </template>
        
    </div>
</div>
