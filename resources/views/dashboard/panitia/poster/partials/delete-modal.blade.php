<div class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm" x-show="isDeleteOpen" x-transition.opacity style="display: none;">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden flex flex-col text-center" @click.away="if(!isSaving) isDeleteOpen = false">
        
        <div class="p-6 pt-8 pb-6">
            <div class="w-16 h-16 rounded-full bg-red-50 text-red-500 mx-auto flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h2 class="text-xl font-black text-gray-900 mb-2">Hapus Poster?</h2>
            <p class="text-sm text-gray-500 mb-1">Anda yakin ingin menghapus poster <strong class="text-gray-800" x-text="selectedPoster?.title"></strong>?</p>
            <p class="text-xs text-red-500 font-medium">Tindakan ini tidak dapat dibatalkan dan gambar akan dihapus dari server.</p>
        </div>

        <div class="p-4 bg-gray-50 flex gap-3">
            <button @click="isDeleteOpen = false" :disabled="isSaving" class="flex-1 py-2.5 rounded-xl font-bold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 transition-colors text-sm disabled:opacity-50">Batal</button>
            <button @click="deletePoster()" :disabled="isSaving" class="flex-1 py-2.5 rounded-xl font-bold text-white bg-red-600 hover:bg-red-700 transition-colors text-sm flex items-center justify-center gap-2 disabled:opacity-50 shadow-sm shadow-red-200">
                <template x-if="isSaving">
                    <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </template>
                <span x-text="isSaving ? 'Menghapus...' : 'Ya, Hapus'"></span>
            </button>
        </div>
    </div>
</div>
