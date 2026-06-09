{{-- MODAL: Register New Team --}}
<div x-show="isRegisterModalOpen" style="display: none;" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div 
        x-show="isRegisterModalOpen" 
        @click.away="isRegisterModalOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden"
    >
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h2 class="font-bold text-gray-800">Daftarkan Tim Baru</h2>
            <button @click="isRegisterModalOpen = false" type="button" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <div class="p-6">
            <form @submit.prevent="handleRegisterTeam">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Pilih Cabang Olahraga <span class="text-red-500">*</span>
                        </label>
                        <select 
                            required
                            x-model="selectedSport"
                            @change="selectedCategory = ''"
                            class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-red-400"
                        >
                            <option value="" disabled>-- Pilih Cabang Olahraga --</option>
                            <template x-for="sport in unregisteredSports" :key="sport.id">
                                <option :value="sport.id" x-text="sport.name"></option>
                            </template>
                        </select>
                    </div>

                    <template x-if="selectedSportObj && selectedSportObj.categories && selectedSportObj.categories.length > 0">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Kategori Olahraga <span class="text-red-500">*</span>
                            </label>
                            <select 
                                required
                                x-model="selectedCategory"
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-red-400"
                            >
                                <option value="" disabled>-- Pilih Kategori --</option>
                                <template x-for="cat in selectedSportObj.categories" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.name + ' (Maks: ' + cat.max_members + ' orang)'"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <template x-if="!selectedCategory && selectedSportObj && (!selectedSportObj.categories || selectedSportObj.categories.length === 0)">
                        <p class="text-xs text-gray-500 mt-2">
                            Maksimal pemain: <span x-text="selectedSportObj.max_members"></span> orang.
                        </p>
                    </template>
                </div>
                
                <div class="mt-8 flex gap-3 justify-end">
                    <button 
                        type="button" 
                        @click="isRegisterModalOpen = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit"
                        :disabled="isSubmitting || !selectedSport || (selectedSportObj && selectedSportObj.categories && selectedSportObj.categories.length > 0 && !selectedCategory)"
                        class="px-4 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-hover disabled:opacity-50"
                    >
                        <span x-show="!isSubmitting">Buat Draft Tim</span>
                        <span x-show="isSubmitting">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: Manage Team Players --}}
<div x-show="isManageModalOpen && activeTeam" style="display: none;" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div 
        x-show="isManageModalOpen && activeTeam" 
        @click.away="isManageModalOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        class="bg-white rounded-xl shadow-xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]"
    >
        <template x-if="activeTeam">
            <div class="flex flex-col h-full overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 shrink-0">
                    <div>
                        <h2 class="font-bold text-gray-800 flex items-center gap-2">
                            <span x-text="activeTeam.sport_category ? (activeTeam.sport?.name + ' - ' + activeTeam.sport_category.name) : activeTeam.sport?.name"></span>
                            <span 
                                class="inline-flex text-[10px] px-2 py-0.5 rounded border font-semibold"
                                :class="getStatusStyle(activeTeam.status)"
                                x-text="getStatusDisplay(activeTeam.status)"
                            ></span>
                        </h2>
                        <p class="text-xs text-gray-500 mt-1">Kelola anggota tim untuk cabang olahraga ini.</p>
                    </div>
                    <button @click="isManageModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="flex flex-col md:flex-row flex-1 overflow-hidden min-h-[500px]">
                    {{-- Left Side: Current Team Members --}}
                    <div class="w-full md:w-1/2 flex flex-col border-r border-gray-100 bg-white">
                        <div class="p-4 border-b border-gray-100 shrink-0 flex items-center justify-between">
                            <h3 class="text-sm font-bold text-gray-700">Anggota Tim</h3>
                            <span 
                                class="text-xs font-medium px-2 py-1 rounded-md"
                                :class="activeTeam.current_members >= activeTeam.max_members ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                            >
                                <span x-text="activeTeam.current_members"></span> / <span x-text="activeTeam.max_members"></span>
                            </span>
                        </div>
                        <div class="p-4 overflow-y-auto flex-1 bg-gray-50/30">
                            <template x-if="!activeTeam.players || activeTeam.players.length === 0">
                                <div class="text-center py-8 text-gray-400">
                                    <p class="text-sm">Belum ada anggota tim.</p>
                                    <p class="text-xs mt-1">Tambahkan dari daftar di sebelah kanan.</p>
                                </div>
                            </template>
                            
                            <template x-if="activeTeam.players && activeTeam.players.length > 0">
                                <ul class="space-y-2">
                                    <template x-for="player in activeTeam.players" :key="player.id">
                                        <li class="flex items-center justify-between bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800" x-text="player.name"></p>
                                                <p class="text-xs text-gray-500" x-text="player.nim_nip"></p>
                                                <div class="mt-1">
                                                    <span 
                                                        class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded border"
                                                        :class="getRiskBadgeStyle(player.risk_lvl)"
                                                        x-text="getRiskBadgeText(player.risk_lvl)"
                                                    ></span>
                                                </div>
                                            </div>
                                            <template x-if="activeTeam.status === 'draft'">
                                                <button 
                                                    @click="handleRemovePlayer(activeTeam.id, player.id)"
                                                    class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                                                    title="Hapus dari tim"
                                                >
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </template>
                                        </li>
                                    </template>
                                </ul>
                            </template>
                        </div>
                        
                        {{-- Submit to Panitia Button --}}
                        <template x-if="activeTeam.status === 'draft'">
                            <div class="p-4 border-t border-gray-100 bg-white">
                                <button
                                    @click="handleSubmitTeam(activeTeam.id)"
                                    :disabled="!activeTeam.players || activeTeam.players.length === 0"
                                    class="w-full flex justify-center items-center gap-2 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Ajukan Tim ke Panitia
                                </button>
                                <template x-if="!activeTeam.players || activeTeam.players.length === 0">
                                    <p class="text-[10px] text-center text-gray-400 mt-2">
                                        Tambahkan minimal 1 pemain untuk mengajukan tim.
                                    </p>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Right Side: Available Contingent Members --}}
                    <div class="w-full md:w-1/2 flex flex-col bg-gray-50/50">
                        <div class="p-4 border-b border-gray-100 shrink-0">
                            <h3 class="text-sm font-bold text-gray-700 mb-3">Tersedia di Kontingen</h3>
                            <div class="relative">
                                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input 
                                    type="text" 
                                    placeholder="Cari nama atau NIM..."
                                    :disabled="activeTeam.status !== 'draft'"
                                    class="w-full pl-8 pr-3 py-1.5 bg-white border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-red-400 disabled:bg-gray-100"
                                    x-model="playerSearchTerm"
                                />
                            </div>
                        </div>
                        
                        <div class="p-4 overflow-y-auto flex-1">
                            <template x-if="activeTeam.status !== 'draft'">
                                <div class="text-center py-8 text-gray-400 px-4">
                                    <svg class="mx-auto text-green-300 mb-2 w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    <p class="text-sm">Tim sudah diajukan.</p>
                                    <p class="text-xs mt-1">Anda tidak dapat menambah atau menghapus anggota lagi.</p>
                                </div>
                            </template>
                            
                            <template x-if="activeTeam.status === 'draft' && activeTeam.current_members >= activeTeam.max_members">
                                <div class="text-center py-8 text-gray-400 px-4">
                                    <svg class="mx-auto text-yellow-300 mb-2 w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="text-sm">Kuota tim sudah penuh.</p>
                                </div>
                            </template>

                            <template x-if="activeTeam.status === 'draft' && activeTeam.current_members < activeTeam.max_members">
                                <ul class="space-y-2">
                                    <template x-for="player in availablePlayers" :key="player.id">
                                        <li class="flex items-center justify-between bg-white p-2.5 rounded-lg border border-gray-200 hover:border-gray-300 transition-colors">
                                            <div class="truncate pr-2">
                                                <p class="text-sm font-medium text-gray-800 truncate" x-text="player.name"></p>
                                                <p class="text-xs text-gray-500" x-text="player.nim_nip"></p>
                                                <div class="mt-1">
                                                    <span 
                                                        class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded border"
                                                        :class="getRiskBadgeStyle(player.risk_lvl)"
                                                        x-text="getRiskBadgeText(player.risk_lvl)"
                                                    ></span>
                                                </div>
                                            </div>
                                            <button 
                                                @click="handleAddPlayer(player.id)"
                                                class="flex items-center gap-1 text-[11px] font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-2 py-1.5 rounded transition-colors shrink-0"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                                Tambah
                                            </button>
                                        </li>
                                    </template>
                                    <template x-if="availablePlayers.length === 0">
                                        <div class="text-center py-4 text-xs text-gray-400">
                                            Tidak ada anggota yang cocok dengan pencarian / semua anggota sudah terdaftar.
                                        </div>
                                    </template>
                                </ul>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
