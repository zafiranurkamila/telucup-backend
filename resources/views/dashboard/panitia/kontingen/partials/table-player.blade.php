<table class="w-full text-left text-sm text-gray-600">
    <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-200">
        <tr>
            <th class="px-6 py-4">Nama & Akun Player</th>
            <th class="px-6 py-4">NIM / NIP</th>
            <th class="px-6 py-4">Status Civitas</th>
            <th class="px-6 py-4">Status Risiko</th>
            <th class="px-6 py-4">Kontingen</th>
            <th class="px-6 py-4 text-right">Aksi</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        <template x-if="isLoading">
            <tr><td colspan="6" class="px-6 py-4 text-center">Memuat...</td></tr>
        </template>
        <template x-if="!isLoading && filteredPlayers.length === 0">
            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">Tidak ada data Player</td></tr>
        </template>
        <template x-if="!isLoading && filteredPlayers.length > 0">
            <template x-for="player in filteredPlayers" :key="player.id">
                <tr class="hover:bg-gray-50 group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-100 overflow-hidden shrink-0 flex items-center justify-center text-emerald-600">
                                <template x-if="player.photo_path">
                                    <img :src="player.photo_path" :alt="player.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!player.photo_path">
                                    <span class="font-bold text-sm" x-text="player.name ? player.name.charAt(0).toUpperCase() : '?'"></span>
                                </template>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800" x-text="player.name"></div>
                                <div class="text-xs text-gray-400" x-text="player.user?.email"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4" x-text="player.nim_nip || '-'"></td>
                    <td class="px-6 py-4">
                        <span x-text="player.employee_status === 'student' ? 'Mahasiswa' : (player.employee_status === 'employee' ? 'Karyawan / Dosen' : (player.employee_status || '-'))"></span>
                    </td>
                    <td class="px-6 py-4">
                        <span :class="getRiskBadgeClasses(player.risk_lvl)" class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full border" x-text="getRiskBadgeText(player.risk_lvl)"></span>
                    </td>
                    <td class="px-6 py-4">
                        <template x-if="player.contingent">
                            <span x-text="player.contingent.name"></span>
                        </template>
                        <template x-if="!player.contingent">
                            <span class="text-gray-400 text-xs italic">N/A</span>
                        </template>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex flex-col sm:flex-row gap-2 justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="openAssignPlayerContingent(player)" class="text-[11px] flex items-center gap-1 font-medium text-emerald-600 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2 py-1 rounded transition-colors w-full sm:w-auto justify-center" title="Assign Kontingen">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                Assign
                            </button>
                            <template x-if="player.user?.role !== 'pic_kontingen'">
                                <button @click="handlePromoteToPic(player)" class="text-[11px] flex items-center gap-1 font-medium text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded transition-colors w-full sm:w-auto justify-center" title="Promote Player to PIC">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                    Jadikan PIC
                                </button>
                            </template>
                        </div>
                    </td>
                </tr>
            </template>
        </template>
    </tbody>
</table>
