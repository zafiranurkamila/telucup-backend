<div class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm" x-show="isUploadModalOpen" x-transition.opacity style="display: none;">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col" @click.away="if(!uploading) isUploadModalOpen = false">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50">
            <h2 class="text-lg font-bold text-gray-800">Upload Foto Dokumentasi</h2>
            <button @click="isUploadModalOpen = false" :disabled="uploading" class="text-gray-400 hover:text-gray-700 hover:bg-gray-200 p-1.5 rounded-full transition-colors disabled:opacity-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6" x-data="{ file: null, preview: null, isDragging: false, handleUpload(e) { 
            const f = e.target.files[0]; 
            if(f) { 
                this.file = f; 
                this.preview = URL.createObjectURL(f); 
            } 
        } }" x-init="$watch('isUploadModalOpen', val => { if(!val){ file=null; preview=null; } })">
            
            <div class="relative border-2 border-dashed rounded-2xl bg-gray-50 hover:bg-gray-100 transition-colors group cursor-pointer overflow-hidden" 
                 :class="isDragging ? 'border-[#b71c1c] bg-red-50' : 'border-gray-300'"
                 @dragover.prevent="isDragging = true"
                 @dragleave.prevent="isDragging = false"
                 @drop.prevent="isDragging = false; const dt = $event.dataTransfer; if(dt.files[0]) { file = dt.files[0]; preview = URL.createObjectURL(file); }"
            >
                <input type="file" accept="image/jpeg,image/png,image/webp,image/jpg" @change="handleUpload" :disabled="uploading" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer disabled:cursor-not-allowed z-10">
                
                <div class="p-8 flex flex-col items-center justify-center text-center">
                    <template x-if="preview">
                        <div class="w-full aspect-video rounded-xl overflow-hidden shadow-sm relative">
                            <img :src="preview" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="text-white font-medium text-sm">Ganti File</span>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="!preview">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 rounded-full bg-red-50 text-[#b71c1c] flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>
                            <p class="text-gray-900 font-bold">Klik atau seret file ke sini</p>
                            <p class="text-sm text-gray-500 mt-1">Mendukung JPG, PNG, WEBP (Maks 5MB)</p>
                        </div>
                    </template>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button @click="isUploadModalOpen = false" :disabled="uploading" class="px-5 py-2.5 rounded-xl font-medium text-gray-600 hover:bg-gray-200 transition-colors text-sm disabled:opacity-50">Batal</button>
                <button @click="uploadPhoto(file)" :disabled="uploading || !file" class="px-5 py-2.5 rounded-xl font-bold text-white bg-[#b71c1c] hover:bg-[#991717] transition-colors text-sm shadow-sm flex items-center gap-2 disabled:opacity-50">
                    <template x-if="uploading">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="uploading ? 'Mengunggah...' : 'Upload'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
