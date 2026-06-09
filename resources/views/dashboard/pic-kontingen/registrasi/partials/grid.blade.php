<div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-gray-50/50">
    <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
        <div class="relative w-full sm:w-64">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input 
                type="text" 
                placeholder="Cari cabang olahraga..."
                class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-red-400 transition-shadow"
                x-model="searchTerm"
            />
        </div>
        <select 
            x-model="sportFilter"
            class="w-full sm:w-auto px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-red-400 transition-shadow text-gray-600"
        >
            <option value="">Semua Cabang Olahraga</option>
            <template x-for="sport in availableSports" :key="sport.id">
                <option :value="sport.id" x-text="sport.name"></option>
            </template>
        </select>
        <select 
            x-model="statusFilter"
            class="w-full sm:w-auto px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-red-400 transition-shadow text-gray-600"
        >
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="submitted">Menunggu Verifikasi</option>
            <option value="verified">Terverifikasi</option>
            <option value="rejected">Ditolak</option>
        </select>
    </div>
</div>

<template x-if="isLoading">
    <div class="p-12 flex justify-center items-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand"></div>
    </div>
</template>

<template x-if="!isLoading">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 p-5">
        <template x-for="team in filteredTeams" :key="team.id">
            <div class="flex flex-col bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                {{-- Header --}}
                <div class="p-4 border-b border-gray-100">
                    <div class="flex items-start justify-between mb-2">
                        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center text-brand shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        </div>
                        <span 
                            class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-full border"
                            :class="getStatusStyle(team.status)"
                        >
                            <span x-text="getStatusDisplay(team.status)"></span>
                        </span>
                    </div>
                    <h3 class="font-bold text-gray-800 line-clamp-1">
                        <span x-text="team.sport_category ? (team.sport?.name + ' - ' + team.sport_category.name) : team.sport?.name"></span>
                    </h3>
                    
                    <template x-if="team.status === 'rejected' && team.reject_reason">
                        <div class="mt-2 text-xs text-red-600 bg-red-50 p-2 rounded border border-red-100 flex items-start gap-1.5">
                            <svg class="w-3.5 h-3.5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="team.reject_reason"></span>
                        </div>
                    </template>
                </div>

                {{-- Body --}}
                <div class="p-4 flex-1">
                    <div class="flex items-center justify-between text-sm mb-3">
                        <span class="text-gray-500 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Anggota Terdaftar
                        </span>
                        <span class="font-semibold text-gray-700">
                            <span x-text="team.current_members"></span> 
                            <span class="text-gray-400 font-normal" x-text="'/ ' + team.max_members"></span>
                        </span>
                    </div>

                    <div class="w-full bg-gray-100 rounded-full h-2 mb-4 overflow-hidden">
                        <div 
                            class="h-2 rounded-full transition-all duration-300"
                            :class="team.current_members >= team.max_members ? 'bg-green-500' : 'bg-blue-500'"
                            :style="`width: ${(team.current_members / team.max_members) * 100}%`"
                        ></div>
                    </div>

                    <div class="space-y-2">
                        <template x-for="player in (team.players || []).slice(0, 3)" :key="player.id">
                            <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-gray-50 border border-gray-100">
                                <span class="font-medium text-gray-700 truncate mr-2" x-text="player.name"></span>
                                <span class="text-gray-400 shrink-0" x-text="player.nim_nip"></span>
                            </div>
                        </template>
                        
                        <template x-if="(team.players || []).length > 3">
                            <div class="text-xs text-center text-gray-500 font-medium pt-1">
                                + <span x-text="team.players.length - 3"></span> anggota lainnya
                            </div>
                        </template>
                        
                        <template x-if="(team.players || []).length === 0">
                            <div class="text-xs text-center text-gray-400 italic p-3 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                                Belum ada anggota tim
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                    <button 
                        @click="activeTeamId = team.id; isManageModalOpen = true;"
                        class="w-full py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2"
                        :class="team.status === 'draft' 
                            ? 'bg-white border border-brand text-brand hover:bg-red-50' 
                            : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50'"
                    >
                        <span x-text="team.status === 'draft' ? 'Kelola Anggota Tim' : 'Lihat Detail Tim'"></span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>

<template x-if="!isLoading && filteredTeams.length === 0">
    <div class="text-center py-12">
        <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
        </div>
        <h3 class="text-lg font-medium text-gray-800">Tidak ada tim yang ditemukan</h3>
        <p class="text-gray-500 text-sm mt-1">Belum ada tim yang didaftarkan untuk kontingen ini.</p>
    </div>
</template>
