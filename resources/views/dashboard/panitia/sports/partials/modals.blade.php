{{-- Modal Base Style --}}
<style>
    .modal-overlay { background-color: rgba(0, 0, 0, 0.5); }
    .modal-content { max-height: 90vh; overflow-y: auto; }
</style>

{{-- 1. Sport Form Modal (Create & Edit) --}}
<div x-show="isFormModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 transition-opacity" style="display: none;">
    <div class="fixed inset-0 modal-overlay" @click="isFormModalOpen = false"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col modal-content z-10" @click.stop>
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800" x-text="sportToEdit ? 'Edit Cabang Olahraga' : 'Tambah Cabang Olahraga'"></h3>
            <button @click="isFormModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto p-5">
            <template x-if="error">
                <div class="mb-4 p-3 bg-red-50 text-red-600 text-sm rounded-lg border border-red-100" x-text="error"></div>
            </template>
            
            <form @submit.prevent="handleSubmitForm">
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Cabang Olahraga <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.name" placeholder="cth: Badminton" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-brand focus:ring-1 focus:ring-red-100" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Maksimal Pemain Keseluruhan</label>
                            <input type="number" x-model="formData.max_members" placeholder="Kosongkan jika tidak ada batas" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-brand focus:ring-1 focus:ring-red-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Icon / Logo</label>
                            <input type="file" accept="image/*" @change="handleIconChange" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:outline-none focus:border-brand focus:ring-1 focus:ring-red-100 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-brand hover:file:bg-red-100">
                            <template x-if="sportToEdit && sportToEdit.icon_path">
                                <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengubah icon.</p>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Dynamic Category Section --}}
                <div class="mb-6 border border-gray-200 rounded-xl p-4 bg-gray-50/50">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-bold text-gray-800">Kategori Perlombaan</h4>
                        <button type="button" @click="addCategory" class="text-xs font-medium bg-white border border-gray-200 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-50 flex items-center gap-1 transition-colors shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Tambah Kategori
                        </button>
                    </div>

                    <template x-if="formData.categories.length === 0">
                        <div class="text-center py-6 text-sm text-gray-400 bg-white border border-dashed border-gray-200 rounded-lg">
                            Belum ada kategori yang ditambahkan.
                        </div>
                    </template>
                    
                    <template x-if="formData.categories.length > 0">
                        <div class="space-y-3">
                            <template x-for="(cat, index) in formData.categories" :key="index">
                                <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                                    <div class="flex-1 w-full">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="cat.name" placeholder="cth: Tunggal Putra" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:outline-none focus:border-brand" required>
                                    </div>
                                    <div class="w-full sm:w-32">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Maks Pemain</label>
                                        <input type="number" x-model="cat.max_members" placeholder="cth: 1" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:outline-none focus:border-brand">
                                    </div>
                                    <button type="button" @click="removeCategory(index)" class="mt-5 sm:mt-5 p-2 text-red-500 hover:bg-red-50 rounded transition-colors self-end" title="Hapus Kategori">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="isFormModalOpen = false" :disabled="isSubmitting" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Batal</button>
                    <button type="submit" :disabled="isSubmitting" class="px-4 py-2 text-sm font-medium text-white bg-brand hover:bg-brand-hover rounded-lg transition-colors flex items-center gap-2 disabled:opacity-50">
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Data'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. Delete Sport Modal --}}
<div x-show="sportToDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4 transition-opacity" style="display: none;">
    <div class="fixed inset-0 modal-overlay" @click="sportToDelete = null"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden flex flex-col modal-content z-10" @click.stop>
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800">Hapus Cabang Olahraga</h3>
            <button @click="sportToDelete = null" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-5">
            <div class="flex items-center gap-3 mb-4 text-red-600 bg-red-50 p-3 rounded-lg border border-red-100">
                <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <p class="text-sm">Anda yakin ingin menghapus cabang olahraga <strong x-text="sportToDelete?.name"></strong>?</p>
            </div>
            <p class="text-sm text-gray-500 mb-6">
                Tindakan ini tidak dapat dibatalkan. Menghapus cabang olahraga akan menghapus data kategori yang terkait dengannya.
            </p>
            
            <template x-if="error">
                <p class="text-xs text-red-500 mb-4" x-text="error"></p>
            </template>
            
            <div class="flex justify-end gap-3">
                <button type="button" @click="sportToDelete = null" :disabled="isSubmitting" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Batal
                </button>
                <button @click="handleDelete" :disabled="isSubmitting" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors flex items-center gap-2 disabled:opacity-70">
                    <span x-text="isSubmitting ? 'Menghapus...' : 'Ya, Hapus'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
