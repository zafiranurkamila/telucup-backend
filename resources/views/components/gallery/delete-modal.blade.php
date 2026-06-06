{{-- Delete Folder Modal --}}
<div class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm" x-show="folderToDelete !== null" x-transition.opacity style="display: none;">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden flex flex-col text-center" @click.away="folderToDelete = null">
        <div class="p-6 pt-8 pb-6">
            <div class="w-16 h-16 rounded-full bg-red-50 text-red-500 mx-auto flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h2 class="text-xl font-black text-gray-900 mb-2">Hapus Folder?</h2>
            <p class="text-sm text-gray-500 mb-1">Apakah Anda yakin ingin menghapus folder <strong class="text-gray-800" x-text="folderToDelete?.name"></strong>?</p>
            <p class="text-xs text-red-500 font-medium mt-2">Folder ini hanya bisa dihapus jika isinya kosong.</p>
        </div>

        <div class="p-4 bg-gray-50 flex gap-3">
            <button @click="folderToDelete = null" class="flex-1 py-2.5 rounded-xl font-bold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 transition-colors text-sm">Batal</button>
            <button @click="deleteFolder()" class="flex-1 py-2.5 rounded-xl font-bold text-white bg-red-600 hover:bg-red-700 transition-colors text-sm shadow-sm">Ya, Hapus</button>
        </div>
    </div>
</div>

{{-- Delete Photo Modal --}}
<div class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm" x-show="photoToDelete !== null" x-transition.opacity style="display: none;">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden flex flex-col text-center" @click.away="photoToDelete = null">
        <div class="p-6 pt-8 pb-6">
            <template x-if="photoToDelete">
                <div class="w-24 h-24 mx-auto rounded-xl overflow-hidden mb-4 shadow-sm">
                    <img :src="photoToDelete.image_url" class="w-full h-full object-cover">
                </div>
            </template>
            <h2 class="text-xl font-black text-gray-900 mb-2">Hapus Foto Event?</h2>
            <p class="text-sm text-gray-500">Foto ini akan dihapus secara permanen dari server. Tindakan ini tidak dapat dibatalkan.</p>
        </div>

        <div class="p-4 bg-gray-50 flex gap-3">
            <button @click="photoToDelete = null" class="flex-1 py-2.5 rounded-xl font-bold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 transition-colors text-sm">Batal</button>
            <button @click="deletePhoto()" class="flex-1 py-2.5 rounded-xl font-bold text-white bg-red-600 hover:bg-red-700 transition-colors text-sm shadow-sm">Ya, Hapus</button>
        </div>
    </div>
</div>
