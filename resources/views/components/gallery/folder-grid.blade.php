<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-800" x-text="currentFolderId === null ? 'Folder Utama' : 'Subfolder'"></h2>
    </div>

    <template x-if="folders.length === 0">
        <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center bg-gray-50/50">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <h3 class="text-gray-900 font-medium mb-1">Belum ada folder</h3>
            <p class="text-gray-500 text-sm mb-4">Buat folder baru untuk mengorganisir foto event Anda.</p>
            <template x-if="!isViewer">
                <button @click="openFolderModal()" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm font-semibold shadow-sm hover:bg-gray-50 transition-colors">
                    + Buat Folder
                </button>
            </template>
        </div>
    </template>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" x-show="folders.length > 0">
        <template x-for="folder in filteredFolders" :key="folder.id">
            <div class="group bg-white border border-gray-200 rounded-xl p-4 hover:border-[#b71c1c]/30 hover:shadow-md transition-all cursor-pointer relative flex items-center gap-4" @click="openFolder(folder.id)">
                
                <div class="w-12 h-12 rounded-lg bg-red-50 text-[#b71c1c] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg>
                </div>

                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-900 text-sm truncate group-hover:text-[#b71c1c] transition-colors" x-text="folder.name"></h3>
                    <p class="text-xs text-gray-500 mt-0.5 truncate"><span x-text="folder.photos_count || 0"></span> Foto • <span x-text="folder.children_count || 0"></span> Subfolder</p>
                </div>

                <template x-if="!isViewer">
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="relative" x-data="{ open: false }" @click.stop>
                            <button @click="open = !open" @click.outside="open = false" class="p-1.5 text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                            </button>
                            
                            <div x-show="open" x-transition.opacity class="absolute right-0 mt-1 w-36 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-20" style="display: none;">
                                <button @click="open = false; openFolderModal(folder)" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    Ubah Nama
                                </button>
                                <button @click="open = false; confirmDeleteFolder(folder)" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 font-medium">
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
