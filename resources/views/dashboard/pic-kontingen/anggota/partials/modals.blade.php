{{-- Modal Add Member --}}
<div x-show="isAddModalOpen" style="display: none;" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div 
        x-show="isAddModalOpen" 
        @click.away="isAddModalOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden"
    >
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h2 class="font-bold text-gray-800">Tambah Anggota Kontingen</h2>
            <button @click="isAddModalOpen = false" type="button" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="p-6">
            <form @submit.prevent="handleAddMember">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            required
                            class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-red-400"
                            x-model="newMember.name"
                            placeholder="Masukkan nama lengkap"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Email SSO <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            required
                            class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-red-400"
                            x-model="newMember.email"
                            placeholder="email@telkomuniversity.ac.id"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Password / NIM / NIP <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            required
                            class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-red-400"
                            x-model="newMember.password"
                            placeholder="Masukkan password atau NIM"
                        />
                        <p class="text-[10px] text-gray-500 mt-1">Password akan berfungsi sebagai NIM/NIP untuk verifikasi awal.</p>
                    </div>
                </div>
                
                <div class="mt-6 p-3 bg-blue-50 border border-blue-100 rounded-lg flex items-start gap-2 text-xs text-blue-800">
                    <svg class="shrink-0 mt-0.5 text-blue-500 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <p>Sistem akan membuatkan akun player otomatis berdasarkan email SSO. Akun akan langsung ditautkan ke kontingen Anda.</p>
                </div>

                <div class="mt-8 flex gap-3 justify-end">
                    <button 
                        type="button" 
                        @click="isAddModalOpen = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit"
                        :disabled="isSubmitting"
                        class="px-4 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover disabled:opacity-50"
                    >
                        <span x-show="!isSubmitting">Buat Akun & Tambahkan</span>
                        <span x-show="isSubmitting">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
