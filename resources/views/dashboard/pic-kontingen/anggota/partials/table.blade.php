{{-- Filters & Search --}}
<div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-gray-50/50">
    <div class="relative w-full sm:w-72">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input 
            type="text" 
            placeholder="Cari nama atau NIM/NIP..."
            class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-100 focus:border-red-400 transition-shadow"
            x-model="searchTerm"
        />
    </div>
    <div class="flex flex-wrap gap-2 w-full sm:w-auto">
        <template x-for="filter in ['Semua', 'Terverifikasi', 'Pending', 'Draft', 'Ditolak']">
            <button 
                @click="statusFilter = filter"
                :class="statusFilter === filter ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
                class="px-3 py-1.5 text-xs font-medium rounded-full border transition-colors"
                x-text="filter"
            ></button>
        </template>
    </div>
</div>

{{-- Member List - Table View --}}
<div class="overflow-x-auto min-h-[300px]">
    <template x-if="isLoading">
        <div class="p-12 flex justify-center items-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand"></div>
        </div>
    </template>

    <template x-if="!isLoading">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-700 text-xs uppercase font-semibold border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4">Profil Anggota</th>
                    <th class="px-6 py-4">NIM / NIP</th>
                    <th class="px-6 py-4">Status Civitas</th>
                    <th class="px-6 py-4">Status Risiko</th>
                    <th class="px-6 py-4">Status & Kelengkapan</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <template x-for="member in filteredMembers" :key="member.id">
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400 shrink-0 overflow-hidden">
                                    <template x-if="member.photo_path">
                                        <img :src="member.photo_path" :alt="member.name" class="w-full h-full object-cover" onerror="this.style.display='none'">
                                    </template>
                                    <template x-if="!member.photo_path">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </template>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-800" x-text="member.name"></div>
                                    <div class="text-xs text-gray-500 mt-0.5" x-text="(member.user && member.user.email) ? member.user.email : (member.email || '')"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4" x-text="member.nim_nip || '-'"></td>
                        <td class="px-6 py-4">
                            <span x-text="member.employee_status === 'student' ? 'Mahasiswa' : (member.employee_status === 'employee' ? 'Karyawan / Dosen' : (member.employee_status || '-'))"></span>
                        </td>
                        <td class="px-6 py-4">
                            {{-- Risk Badge --}}
                            <template x-if="member.risk_lvl === 'low'">
                                <span class="inline-flex items-center bg-emerald-50 text-emerald-700 text-xs font-semibold px-2.5 py-1 rounded-full border border-emerald-200">Risiko Rendah</span>
                            </template>
                            <template x-if="member.risk_lvl === 'medium'">
                                <span class="inline-flex items-center bg-orange-50 text-orange-700 text-xs font-semibold px-2.5 py-1 rounded-full border border-orange-200">Risiko Sedang</span>
                            </template>
                            <template x-if="member.risk_lvl === 'high'">
                                <span class="inline-flex items-center bg-red-50 text-red-700 text-xs font-semibold px-2.5 py-1 rounded-full border border-red-200">Risiko Tinggi</span>
                            </template>
                            <template x-if="!member.risk_lvl || member.risk_lvl === 'not_yet'">
                                <span class="inline-flex items-center bg-gray-50 text-gray-500 text-xs font-medium px-2.5 py-1 rounded-full border border-gray-200">Belum Mengisi</span>
                            </template>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1.5">
                                <template x-if="member.photo_path && member.work_location">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Data Lengkap
                                    </span>
                                </template>
                                <template x-if="!(member.photo_path && member.work_location)">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-orange-600">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Data Belum Lengkap
                                    </span>
                                </template>
                                
                                {{-- Currently Next.js links to /player/{id}. In Laravel we might just point there if route exists, or # for now. --}}
                                <a :href="'/dashboard/player/'" class="text-[10px] text-blue-600 hover:underline font-medium uppercase tracking-wider w-fit">
                                    Lihat / Lengkapi Data
                                </a>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button 
                                    @click="handleRemoveMember(member.id)"
                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Hapus dari kontingen"
                                >
                                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </template>
    
    <template x-if="!isLoading && filteredMembers.length === 0">
        <div class="text-center py-16 px-4">
            <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <h3 class="text-lg font-medium text-gray-800">Tidak ada anggota ditemukan</h3>
            <p class="text-gray-500 text-sm mt-1">Gunakan kata kunci pencarian lain atau tambahkan anggota baru.</p>
        </div>
    </template>
</div>
