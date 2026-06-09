<!-- Filter Toolbar -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <div class="flex flex-col sm:flex-row gap-3">
        <!-- Search -->
        <div class="relative w-full sm:w-64">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model.debounce.300ms="searchQuery" placeholder="Cari nama, kontingen..." class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#B41F2A] focus:ring-1 focus:ring-[#B41F2A] transition-shadow">
        </div>
        
        <!-- Risk Filter -->
        <select x-model="riskFilter" class="w-full sm:w-40 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#B41F2A] bg-white text-gray-700">
            <option value="">Semua Risiko</option>
            <option value="high">High Risk</option>
            <option value="medium">Medium</option>
            <option value="low">Low Risk</option>
        </select>
        
        <!-- Sport Filter -->
        <select x-model="sportFilter" class="w-full sm:w-48 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#B41F2A] bg-white text-gray-700">
            <option value="">Semua Cabang Olahraga</option>
            <template x-for="s in sportSummary" :key="s.sport_branch">
                <option :value="s.sport_branch" x-text="s.sport_branch"></option>
            </template>
        </select>
        
        <!-- Clearance Filter -->
        <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-600 border border-gray-200 rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors">
            <input type="checkbox" x-model="clearanceFilter" class="rounded text-[#B41F2A] focus:ring-[#B41F2A]">
            Perlu Clearance
        </label>
    </div>
    
    <!-- Clear Filters -->
    <template x-if="searchQuery || riskFilter || clearanceFilter || sportFilter">
        <button @click="searchQuery = ''; riskFilter = ''; clearanceFilter = false; sportFilter = '';" class="text-sm text-gray-500 hover:text-red-600 transition-colors flex items-center gap-1 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Reset Filter
        </button>
    </template>
</div>

<!-- Data Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto min-h-[300px]">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-[11px] uppercase tracking-wider">
                    <th class="p-4 font-bold border-b border-gray-100 w-12 text-center">ID</th>
                    <th class="p-4 font-bold border-b border-gray-100">Pemain & Identitas</th>
                    <th class="p-4 font-bold border-b border-gray-100">Kontingen / Cabor</th>
                    <th class="p-4 font-bold border-b border-gray-100 text-center">Risiko & Skor</th>
                    <th class="p-4 font-bold border-b border-gray-100 text-center">Review Medis</th>
                    <th class="p-4 font-bold border-b border-gray-100 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                <template x-if="loading">
                    <tr><td colspan="6" class="p-10 text-center text-gray-500"><div class="flex justify-center"><div class="w-6 h-6 border-2 border-[#B41F2A] border-t-transparent rounded-full animate-spin"></div></div></td></tr>
                </template>
                <template x-if="!loading && filteredData.length === 0">
                    <tr><td colspan="6" class="p-12 text-center text-gray-500 font-medium">Tidak ada data ditemukan.</td></tr>
                </template>
                <template x-for="assessment in filteredData" :key="assessment.id">
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="p-4 text-center font-medium text-gray-400 text-xs" x-text="'#' + assessment.id"></td>
                        <td class="p-4">
                            <p class="font-bold text-gray-800" x-text="assessment.player_name || 'Tidak diketahui'"></p>
                            <p class="text-[11px] text-gray-500 mt-0.5">
                                <span x-text="assessment.nim_nip || 'NIM/NIP: -'"></span> &bull; <span x-text="formatDateShort(assessment.created_at)"></span>
                            </p>
                        </td>
                        <td class="p-4">
                            <p class="font-semibold text-gray-700" x-text="assessment.contingent || '-'"></p>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="assessment.sport_branch || '-'"></p>
                        </td>
                        <td class="p-4 text-center">
                            <span :class="getRiskBadgeClass(assessment.risk_label)" class="px-2.5 py-1 rounded-md text-[11px] font-bold border" x-text="getRiskLabel(assessment.risk_label)"></span>
                            <div class="text-[10px] text-gray-500 font-medium mt-1.5" x-text="'Skor: ' + (assessment.total_score ? assessment.total_score.toFixed(1) : '-')"></div>
                            <template x-if="assessment.requires_clearance">
                                <div class="mt-1 flex items-center justify-center gap-1 text-[10px] text-red-600 font-bold bg-red-50 py-0.5 rounded px-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Clearance
                                </div>
                            </template>
                        </td>
                        <td class="p-4 text-center">
                            <template x-if="!assessment.medical_review?.reviewed_at">
                                <span class="px-2.5 py-1 bg-gray-100 text-gray-600 rounded-md text-[11px] font-medium border border-gray-200">Belum Direview</span>
                            </template>
                            <template x-if="assessment.medical_review?.reviewed_at">
                                <span :class="assessment.medical_review.is_allowed_to_play ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'" class="px-2.5 py-1 rounded-md text-[11px] font-bold border flex items-center justify-center gap-1 w-max mx-auto">
                                    <svg x-show="assessment.medical_review.is_allowed_to_play" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <svg x-show="!assessment.medical_review.is_allowed_to_play" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    <span x-text="assessment.medical_review.is_allowed_to_play ? 'Diizinkan' : 'Dilarang'"></span>
                                </span>
                            </template>
                        </td>
                        <td class="p-4 text-right">
                            <button @click="openDetail(assessment)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-[#B41F2A] hover:text-white transition-colors border border-gray-200 hover:border-transparent">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <template x-if="totalPages > 1 && !searchQuery">
        <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between bg-gray-50">
            <span class="text-xs text-gray-500 font-medium" x-text="`Halaman ${page} dari ${totalPages}`"></span>
            <div class="flex gap-1">
                <button @click="if(page > 1) page--" :disabled="page <= 1" class="px-3 py-1.5 border border-gray-200 rounded bg-white text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 transition-colors">Prev</button>
                <button @click="if(page < totalPages) page++" :disabled="page >= totalPages" class="px-3 py-1.5 border border-gray-200 rounded bg-white text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 transition-colors">Next</button>
            </div>
        </div>
    </template>
</div>
