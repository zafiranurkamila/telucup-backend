<div class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm" x-show="photoToMove !== null" x-transition.opacity style="display: none;">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden flex flex-col" @click.away="photoToMove = null">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50">
            <h2 class="text-lg font-bold text-gray-800">Pindah Foto ke Folder</h2>
            <button @click="photoToMove = null" class="text-gray-400 hover:text-gray-700 hover:bg-gray-200 p-1.5 rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form x-data="{ targetFolderId: '' }" @submit.prevent="movePhoto(targetFolderId)" x-init="$watch('photoToMove', val => { if(val) targetFolderId = val.gallery_folder_id || ''; })">
            <div class="p-6">
                
                <div class="flex items-center gap-4 mb-6 p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <template x-if="photoToMove">
                        <img :src="photoToMove.image_url" class="w-16 h-16 object-cover rounded-lg shadow-sm shrink-0">
                    </template>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">Memindahkan 1 foto</p>
                        <p class="text-xs text-gray-500 truncate" x-text="photoToMove ? ('Dari: ' + (photoToMove.gallery_folder_id ? 'Subfolder' : 'Folder Utama')) : ''"></p>
                    </div>
                </div>

                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">Pilih Folder Tujuan</label>
                <select x-model="targetFolderId" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-[#b71c1c]/20 focus:border-[#b71c1c] transition-all">
                    <option value="">-- Folder Utama --</option>
                    <template x-for="f in allFolders" :key="f.id">
                        <option :value="f.id" x-text="f.name" :disabled="photoToMove && photoToMove.gallery_folder_id == f.id"></option>
                    </template>
                </select>
                <p class="text-xs text-gray-500 mt-2">Pilih folder yang tersedia untuk memindahkan foto ini ke dalam direktori tersebut.</p>
            </div>

            <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                <button type="button" @click="photoToMove = null" class="px-5 py-2.5 rounded-xl font-medium text-gray-600 hover:bg-gray-200 transition-colors text-sm">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-white bg-[#b71c1c] hover:bg-[#991717] transition-colors text-sm shadow-sm" :disabled="photoToMove && photoToMove.gallery_folder_id == targetFolderId">
                    Pindahkan
                </button>
            </div>
        </form>
    </div>
</div>
