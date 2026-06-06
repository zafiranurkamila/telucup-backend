<div class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm" x-show="isFolderModalOpen" x-transition.opacity style="display: none;">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden flex flex-col" @click.away="isFolderModalOpen = false">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50">
            <h2 class="text-lg font-bold text-gray-800" x-text="folderToEdit ? 'Ubah Nama Folder' : 'Buat Folder Baru'"></h2>
            <button @click="isFolderModalOpen = false" class="text-gray-400 hover:text-gray-700 hover:bg-gray-200 p-1.5 rounded-full transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form x-data="{ folderName: '' }" @submit.prevent="saveFolder(folderName)" x-init="$watch('isFolderModalOpen', val => { if(val) folderName = folderToEdit ? folderToEdit.name : ''; setTimeout(() => { if($refs.folderInput) $refs.folderInput.focus() }, 100) })">
            <div class="p-6">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">Nama Folder <span class="text-red-500">*</span></label>
                <input x-ref="folderInput" type="text" x-model="folderName" placeholder="Contoh: Hari ke-1 Futsal" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-[#b71c1c]/20 focus:border-[#b71c1c] transition-all" required>
            </div>

            <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                <button type="button" @click="isFolderModalOpen = false" class="px-5 py-2.5 rounded-xl font-medium text-gray-600 hover:bg-gray-200 transition-colors text-sm">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-white bg-[#b71c1c] hover:bg-[#991717] transition-colors text-sm shadow-sm">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
