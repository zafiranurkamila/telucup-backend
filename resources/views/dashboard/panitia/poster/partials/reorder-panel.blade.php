<div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm relative">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Urutkan Poster</h2>
            <p class="text-sm text-gray-500">Gunakan panah ke atas/bawah untuk mengubah urutan poster pada carousel.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="toggleReorderMode()" :disabled="isSaving" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors text-sm disabled:opacity-50">Batal</button>
            <button @click="saveReorder()" :disabled="isSaving" class="px-5 py-2 bg-[#B41F2A] hover:bg-[#961F23] text-white font-bold rounded-xl transition-colors text-sm flex items-center gap-2 shadow-sm shadow-red-200 disabled:opacity-50">
                <template x-if="isSaving">
                    <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </template>
                <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Urutan'"></span>
            </button>
        </div>
    </div>

    <div class="space-y-3">
        <template x-for="(poster, index) in posters" :key="poster.id">
            <div class="flex items-center gap-4 bg-gray-50 border border-gray-200 rounded-xl p-3 transition-all" :class="isSaving ? 'opacity-50' : ''">
                
                <div class="flex flex-col gap-1 items-center justify-center text-gray-400">
                    <button @click="moveUp(index)" :disabled="isSaving || index === 0" class="p-1 hover:text-gray-800 hover:bg-gray-200 rounded transition-colors disabled:opacity-30 disabled:hover:bg-transparent">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                    </button>
                    <span class="text-xs font-bold w-6 text-center" x-text="index + 1"></span>
                    <button @click="moveDown(index)" :disabled="isSaving || index === posters.length - 1" class="p-1 hover:text-gray-800 hover:bg-gray-200 rounded transition-colors disabled:opacity-30 disabled:hover:bg-transparent">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </div>

                <div class="w-16 h-20 rounded-lg overflow-hidden shrink-0 border border-gray-200 bg-white">
                    <img :src="poster.image_url" class="w-full h-full object-cover">
                </div>

                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-900 text-sm truncate" x-text="poster.title"></h3>
                    <p class="text-xs text-gray-500 truncate mt-0.5" x-text="poster.description || 'Tidak ada deskripsi'"></p>
                    <div class="mt-2 flex items-center gap-2">
                        <span :class="poster.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-600'" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider" x-text="poster.is_active ? 'Aktif' : 'Nonaktif'"></span>
                    </div>
                </div>

                <div class="shrink-0 px-4">
                    <div class="cursor-move text-gray-300 hover:text-gray-500" title="Anda juga dapat menggunakan panah di kiri">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                    </div>
                </div>
            </div>
        </template>
    </div>
    
    <template x-if="isSaving">
        <div class="absolute inset-0 bg-white/50 backdrop-blur-sm z-10 flex items-center justify-center rounded-2xl">
            <div class="bg-white px-6 py-4 rounded-xl shadow-lg border border-gray-100 flex items-center gap-3">
                <svg class="w-6 h-6 animate-spin text-[#B41F2A]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span class="font-bold text-gray-800">Menyimpan urutan...</span>
            </div>
        </div>
    </template>
</div>
