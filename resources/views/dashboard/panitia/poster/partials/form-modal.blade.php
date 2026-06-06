<div class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm" x-show="isFormOpen" x-transition.opacity style="display: none;">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col" @click.away="if(!isSaving) isFormOpen = false">
        
        <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50">
            <h2 class="text-lg font-bold text-gray-800" x-text="selectedPoster ? 'Edit Poster' : 'Upload Poster Baru'"></h2>
            <button @click="isFormOpen = false" :disabled="isSaving" class="text-gray-400 hover:text-gray-700 hover:bg-gray-200 p-1.5 rounded-full transition-colors disabled:opacity-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 space-y-5 overflow-y-auto max-h-[70vh]">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">Judul Poster <span class="text-red-500">*</span></label>
                <input type="text" x-model="formPayload.title" :disabled="isSaving" placeholder="Contoh: Junjung Tinggi Sportifitas" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-[#B41F2A]/20 focus:border-[#B41F2A] transition-all disabled:opacity-50">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">Deskripsi Singkat</label>
                <textarea x-model="formPayload.description" :disabled="isSaving" rows="3" placeholder="Sampaikan pesan fair play di sini..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-[#B41F2A]/20 focus:border-[#B41F2A] transition-all resize-none disabled:opacity-50"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">File Gambar <span x-show="!selectedPoster" class="text-red-500">*</span></label>
                <div class="relative border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50 hover:bg-gray-100 transition-colors group cursor-pointer" :class="isSaving ? 'opacity-50 cursor-not-allowed' : ''">
                    <input type="file" accept="image/jpeg,image/png,image/webp,image/jpg" @change="handleImageUpload" :disabled="isSaving" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer disabled:cursor-not-allowed z-10">
                    
                    <div class="p-6 flex flex-col items-center justify-center text-center">
                        <template x-if="formPayload.imagePreviewUrl">
                            <div class="w-32 h-40 rounded-xl overflow-hidden shadow-sm mb-3 relative">
                                <img :src="formPayload.imagePreviewUrl" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="text-white text-xs font-medium">Ganti Gambar</span>
                                </div>
                            </div>
                        </template>
                        
                        <template x-if="!formPayload.imagePreviewUrl">
                            <div class="w-12 h-12 rounded-full bg-[#B41F2A]/10 text-[#B41F2A] flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>
                        </template>

                        <p class="text-sm font-bold text-gray-700" x-text="formPayload.imagePreviewUrl ? 'Klik untuk mengganti gambar' : 'Klik atau seret gambar ke sini'"></p>
                        <p class="text-xs text-gray-500 mt-1">PNG, JPG, WEBP maks 5MB. Rasio 4:5 direkomendasikan.</p>
                    </div>
                </div>
            </div>

            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl border border-gray-100 bg-gray-50 hover:bg-gray-100 transition-colors" :class="isSaving ? 'opacity-50' : ''">
                <input type="checkbox" x-model="formPayload.is_active" :disabled="isSaving" class="w-5 h-5 rounded border-gray-300 text-[#B41F2A] focus:ring-[#B41F2A]">
                <div>
                    <p class="text-sm font-bold text-gray-800">Tampilkan ke Peserta</p>
                    <p class="text-xs text-gray-500">Jika aktif, poster ini akan muncul di carousel akhir assessment.</p>
                </div>
            </label>
        </div>

        <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
            <button @click="isFormOpen = false" :disabled="isSaving" class="px-5 py-2.5 rounded-xl font-medium text-gray-600 hover:bg-gray-200 transition-colors text-sm disabled:opacity-50">Batal</button>
            <button @click="savePoster()" :disabled="isSaving || !formPayload.title || (!selectedPoster && !formPayload.imageFile)" class="px-5 py-2.5 rounded-xl font-bold text-white bg-[#B41F2A] hover:bg-[#961F23] transition-colors text-sm flex items-center gap-2 disabled:opacity-50 shadow-sm shadow-red-200">
                <template x-if="isSaving">
                    <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </template>
                <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Poster'"></span>
            </button>
        </div>
    </div>
</div>
