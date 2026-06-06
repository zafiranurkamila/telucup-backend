<div>
    <div class="flex items-center justify-between mb-4 mt-8">
        <h2 class="text-lg font-bold text-gray-800">Dokumentasi Foto</h2>
    </div>

    <template x-if="photos.length === 0">
        <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center bg-gray-50/50">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-gray-900 font-medium mb-1">Belum ada foto</h3>
            <p class="text-gray-500 text-sm mb-4">Mulai unggah dokumentasi kegiatan di sini.</p>
            <template x-if="!isViewer">
                <button @click="isUploadModalOpen = true" class="px-4 py-2 bg-[#b71c1c] text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-[#991717] transition-colors">
                    Upload Foto
                </button>
            </template>
        </div>
    </template>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4" x-show="photos.length > 0">
        <template x-for="photo in filteredPhotos" :key="photo.id">
            <div class="group relative aspect-square rounded-xl overflow-hidden bg-gray-100 border border-gray-200 cursor-pointer shadow-sm hover:shadow-md transition-all" @click="photoToPreview = photo">
                <img :src="photo.image_url" :alt="'Foto ' + photo.id" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy">
                
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-3">
                    <p class="text-white text-xs font-medium truncate" x-text="'Diunggah: ' + (photo.uploader ? photo.uploader.name : ('ID ' + photo.uploaded_by))"></p>
                    <p class="text-gray-300 text-[10px] mt-0.5 truncate" x-text="new Date(photo.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'})"></p>
                </div>

                <template x-if="!isViewer">
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="relative" x-data="{ open: false }" @click.stop>
                            <button @click="open = !open" @click.outside="open = false" class="p-1.5 text-white bg-black/50 hover:bg-black/70 backdrop-blur-sm rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                            </button>
                            
                            <div x-show="open" x-transition.opacity class="absolute right-0 mt-1 w-36 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-20" style="display: none;">
                                <button @click="open = false; openMoveModal(photo)" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    Pindah Folder
                                </button>
                                <button @click="open = false; confirmDeletePhoto(photo)" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
