<div>
    <template x-if="loading">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="i in 4" :key="i">
                <div class="animate-pulse rounded-2xl bg-white shadow-sm border border-gray-100 overflow-hidden">
                    <div class="aspect-[4/5] bg-gray-200"></div>
                    <div class="p-5">
                        <div class="h-4 w-3/4 bg-gray-200 rounded mb-3"></div>
                        <div class="h-3 w-full bg-gray-100 rounded mb-2"></div>
                        <div class="h-3 w-2/3 bg-gray-100 rounded"></div>
                        <div class="mt-5 border-t border-gray-100 pt-4 flex justify-between">
                            <div class="h-5 w-9 bg-gray-200 rounded-full"></div>
                            <div class="flex gap-2">
                                <div class="h-6 w-6 bg-gray-100 rounded"></div>
                                <div class="h-6 w-6 bg-gray-100 rounded"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </template>

    <template x-if="!loading && error">
        <div class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-2xl border border-gray-100 border-dashed">
            <svg class="w-12 h-12 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Gagal Memuat Data</h3>
            <p class="text-gray-500 max-w-sm mb-6 text-sm" x-text="error"></p>
            <button @click="fetchPosters()" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors text-sm">Coba Lagi</button>
        </div>
    </template>

    <template x-if="!loading && !error && filteredPosters.length === 0">
        <div class="flex flex-col items-center justify-center py-24 text-center bg-white rounded-2xl border border-gray-100 border-dashed">
            <div class="h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Belum ada poster</h3>
            <p class="text-gray-500 max-w-sm mb-6 text-sm">Poster sportifitas yang Anda unggah akan muncul di sini. Poster aktif akan ditampilkan ke peserta.</p>
            <button @click="openForm()" class="px-6 py-2.5 bg-[#B41F2A] hover:bg-[#961F23] text-white font-bold rounded-xl transition-colors shadow-sm shadow-red-200 text-sm">+ Tambah Poster Pertama</button>
        </div>
    </template>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" x-show="!loading && !error && filteredPosters.length > 0">
        <template x-for="poster in filteredPosters" :key="poster.id">
            <div class="group bg-white rounded-2xl shadow-sm hover:shadow-md transition-all border border-gray-100 overflow-hidden flex flex-col h-full relative" :class="!poster.is_active ? 'opacity-75' : ''">
                
                <div class="relative aspect-[4/5] bg-gray-100 overflow-hidden cursor-pointer" @click="openPreview(poster)">
                    <img :src="poster.image_url" :alt="poster.title" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <div class="bg-white/90 backdrop-blur-sm text-gray-900 p-3 rounded-full shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </div>
                    </div>
                </div>

                <div class="p-5 flex-1 flex flex-col">
                    <h3 class="font-bold text-gray-900 text-sm leading-snug line-clamp-2 mb-2" x-text="poster.title"></h3>
                    <p class="text-xs text-gray-500 line-clamp-3 mb-4 flex-1" x-text="poster.description || 'Tidak ada deskripsi'"></p>
                    
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between mt-auto">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" :checked="poster.is_active" @change="toggleStatus(poster.id, poster.is_active)">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-2 text-[10px] font-bold uppercase tracking-wider" :class="poster.is_active ? 'text-emerald-600' : 'text-gray-400'" x-text="poster.is_active ? 'Aktif' : 'Nonaktif'"></span>
                        </label>

                        <div class="flex items-center gap-1">
                            <button @click.stop="openForm(poster)" class="p-1.5 text-gray-400 hover:text-[#B41F2A] hover:bg-red-50 rounded-lg transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <button @click.stop="openDelete(poster)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
