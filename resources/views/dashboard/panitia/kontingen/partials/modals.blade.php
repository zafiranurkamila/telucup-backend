{{-- Modal Base Style --}}
<style>
    .modal-overlay { background-color: rgba(0, 0, 0, 0.5); }
    .modal-content { max-height: 90vh; overflow-y: auto; }
</style>

{{-- 1. Create Kontingen Modal --}}
<div x-show="isCreateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
    <div class="fixed inset-0 modal-overlay" @click="isCreateModalOpen = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full modal-content z-10 p-6" @click.stop>
        <h3 class="text-lg font-bold text-gray-900 mb-4">Tambah Kontingen</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kontingen</label>
                <input type="text" x-model="contingentForm.name" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:border-brand focus:ring-1 focus:ring-red-100 outline-none" placeholder="Masukkan nama kontingen">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Logo Kontingen (Opsional)</label>
                <input type="file" @change="contingentForm.image = $event.target.files[0]" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-brand hover:file:bg-red-100">
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button @click="isCreateModalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
            <button @click="handleCreateContingent()" :disabled="isSubmitting" class="px-4 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover transition-colors disabled:opacity-50 inline-flex items-center gap-2">
                <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan'"></span>
            </button>
        </div>
    </div>
</div>

{{-- 2. Edit Kontingen Modal --}}
<div x-show="editContingentData" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
    <div class="fixed inset-0 modal-overlay" @click="editContingentData = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full modal-content z-10 p-6" @click.stop>
        <h3 class="text-lg font-bold text-gray-900 mb-4">Edit Kontingen</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kontingen</label>
                <input type="text" x-model="contingentForm.name" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:border-brand focus:ring-1 focus:ring-red-100 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Logo Baru (Opsional)</label>
                <input type="file" @change="contingentForm.image = $event.target.files[0]" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-brand hover:file:bg-red-100">
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button @click="editContingentData = null" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
            <button @click="handleEditContingent()" :disabled="isSubmitting" class="px-4 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover transition-colors disabled:opacity-50">
                <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
            </button>
        </div>
    </div>
</div>

{{-- 3. Delete Kontingen Modal --}}
<div x-show="deleteContingentData" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
    <div class="fixed inset-0 modal-overlay" @click="deleteContingentData = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full modal-content z-10 p-6 text-center" @click.stop>
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Kontingen?</h3>
        <p class="text-sm text-gray-500 mb-6">Anda yakin ingin menghapus <strong x-text="deleteContingentData?.name"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
        <div class="flex justify-center gap-3">
            <button @click="deleteContingentData = null" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
            <button @click="handleDeleteContingent()" :disabled="isSubmitting" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50">
                <span x-text="isSubmitting ? 'Menghapus...' : 'Ya, Hapus'"></span>
            </button>
        </div>
    </div>
</div>

{{-- 4. Create Player/User Modal --}}
<div x-show="isPlayerModalOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
    <div class="fixed inset-0 modal-overlay" @click="isPlayerModalOpen = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full modal-content z-10 p-6" @click.stop>
        <h3 class="text-lg font-bold text-gray-900 mb-4">Tambah Pengguna Baru</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" x-model="playerForm.name" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:border-brand focus:ring-1 focus:ring-red-100 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" x-model="playerForm.email" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:border-brand focus:ring-1 focus:ring-red-100 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Sementara</label>
                <input type="text" x-model="playerForm.password" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:border-brand focus:ring-1 focus:ring-red-100 outline-none" placeholder="Minimal 8 karakter">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Peran Awal</label>
                <select x-model="playerForm.role" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:border-brand outline-none">
                    <option value="player">Player</option>
                    <option value="pic_kontingen">PIC Kontingen</option>
                </select>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button @click="isPlayerModalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
            <button @click="handleCreatePlayer()" :disabled="isSubmitting" class="px-4 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover transition-colors disabled:opacity-50">
                <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan User'"></span>
            </button>
        </div>
    </div>
</div>

{{-- 5. Assign PIC Modal --}}
<div x-show="assignPicContingentData" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
    <div class="fixed inset-0 modal-overlay" @click="assignPicContingentData = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full modal-content z-10 p-6" @click.stop>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Pilih PIC untuk Kontingen</h3>
        <p class="text-sm text-gray-500 mb-4" x-text="assignPicContingentData?.name"></p>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Akun PIC</label>
                <select x-model="assignPicForm.user_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:border-brand outline-none">
                    <option value="">-- Pilih PIC --</option>
                    <template x-for="pic in pics" :key="pic.id">
                        <option :value="pic.id" x-text="pic.name + ' (' + pic.email + ')'"></option>
                    </template>
                </select>
            </div>
            <p class="text-xs text-amber-600 bg-amber-50 p-2 rounded border border-amber-100">
                Catatan: Jika user belum ada di daftar, silakan ke tab Daftar Player dan promosikan user menjadi PIC terlebih dahulu, atau tambah Pengguna baru.
            </p>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button @click="assignPicContingentData = null" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
            <button @click="handleAssignPic()" :disabled="isSubmitting" class="px-4 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover transition-colors disabled:opacity-50">
                <span x-text="isSubmitting ? 'Menyimpan...' : 'Tugaskan PIC'"></span>
            </button>
        </div>
    </div>
</div>

{{-- 6. Assign Player to Contingent Modal --}}
<div x-show="assignContingentToPlayerData" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
    <div class="fixed inset-0 modal-overlay" @click="assignContingentToPlayerData = null"></div>
    <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full modal-content z-10 p-6" @click.stop>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Assign Player ke Kontingen</h3>
        <p class="text-sm text-gray-500 mb-4" x-text="'Player: ' + (assignContingentToPlayerData?.name || '')"></p>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kontingen</label>
                <select x-model="assignContingentForm.contingent_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:border-brand outline-none">
                    <option value="">-- Pilih Kontingen --</option>
                    <template x-for="cont in contingents" :key="cont.id">
                        <option :value="cont.id" x-text="cont.name"></option>
                    </template>
                </select>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button @click="assignContingentToPlayerData = null" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Batal</button>
            <button @click="handleAssignPlayerContingent()" :disabled="isSubmitting" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors disabled:opacity-50">
                <span x-text="isSubmitting ? 'Menyimpan...' : 'Assign'"></span>
            </button>
        </div>
    </div>
</div>
