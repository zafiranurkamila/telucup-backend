<x-layouts.dashboard :roleLabel="'Panitia'">
    <x-slot:title>Verifikasi Pendaftaran Tim</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-panitia')
    </x-slot:sidebar>

    <div x-data="verifikasiTimManager()" class="space-y-6 pb-10">
        
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Verifikasi Pendaftaran Tim</h1>
                <p class="text-gray-500 text-sm mt-1">Kelola dan verifikasi pengajuan pendaftaran tim beserta kelengkapan pemainnya.</p>
            </div>
            <button 
                @click="fetchCompliance()"
                class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2 border border-indigo-200"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Cek Kepatuhan Kontingen
            </button>
        </div>

        {{-- Main Content Area --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Toolbar --}}
            <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row gap-4 justify-between items-center bg-gray-50/50">
                <div class="flex gap-2 w-full sm:w-auto overflow-x-auto pb-2 sm:pb-0">
                    <button 
                        @click="filterStatus = 'all'"
                        :class="filterStatus === 'all' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap"
                    >
                        Semua
                    </button>
                    <button 
                        @click="filterStatus = 'submitted'"
                        :class="filterStatus === 'submitted' ? 'bg-yellow-500 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap"
                    >
                        Pending
                    </button>
                    <button 
                        @click="filterStatus = 'verified'"
                        :class="filterStatus === 'verified' ? 'bg-green-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap"
                    >
                        Verified
                    </button>
                    <button 
                        @click="filterStatus = 'rejected'"
                        :class="filterStatus === 'rejected' ? 'bg-red-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap"
                    >
                        Rejected
                    </button>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto shrink-0 flex-col sm:flex-row">
                    <select
                        x-model="filterSport"
                        class="w-full sm:w-48 pl-3 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#a81d22]/20 focus:border-[#a81d22] bg-white text-gray-700"
                    >
                        <option value="">Semua Cabang Olahraga</option>
                        @foreach($sports as $sport)
                            <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                        @endforeach
                    </select>

                    <div class="relative w-full sm:w-64 shrink-0">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input 
                            type="text" 
                            x-model="searchQuery"
                            placeholder="Cari kontingen, cabang..." 
                            class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#a81d22]/20 focus:border-[#a81d22]"
                        />
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto min-h-[300px]">
                <template x-if="isLoading">
                    <div class="flex justify-center items-center h-48 text-gray-500">
                        <div class="w-6 h-6 border-2 border-[#a81d22] border-t-transparent rounded-full animate-spin"></div>
                        <span class="ml-3 font-medium">Memuat data...</span>
                    </div>
                </template>

                <template x-if="!isLoading">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                                <th class="p-4 font-medium">Kontingen</th>
                                <th class="p-4 font-medium">Cabang Olahraga</th>
                                <th class="p-4 font-medium">Tanggal Pengajuan</th>
                                <th class="p-4 font-medium">Status</th>
                                <th class="p-4 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <template x-if="filteredData.length === 0">
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-500">
                                        Tidak ada data pendaftaran yang ditemukan.
                                    </td>
                                </tr>
                            </template>

                            <template x-for="reg in filteredData" :key="reg.id">
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <template x-if="reg.contingent?.image_url">
                                                <img :src="reg.contingent.image_url" alt="Logo" class="w-8 h-8 rounded-md object-cover border border-gray-200 bg-white" />
                                            </template>
                                            <template x-if="!reg.contingent?.image_url">
                                                <div class="w-8 h-8 rounded-md bg-gray-100 flex items-center justify-center text-gray-400 font-bold text-xs" x-text="getInitials(reg.contingent?.name)"></div>
                                            </template>
                                            <div>
                                                <div class="font-semibold text-gray-800" x-text="reg.contingent?.name || '-'"></div>
                                                <div class="text-gray-500 text-xs mt-0.5" x-text="'PIC: ' + (reg.contingent?.pic?.name || '-')"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="text-gray-800 font-medium" x-text="reg.sport?.name || '-'"></div>
                                        <div class="text-gray-500 text-xs mt-0.5 mb-1" x-text="reg.sport_category?.name || '-'"></div>
                                        <span class="text-[11px] font-medium bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded border border-indigo-100">
                                            <span x-text="reg.current_members"></span> / <span x-text="reg.max_members || '?'"></span> Anggota
                                        </span>
                                    </td>
                                    <td class="p-4 text-gray-600" x-text="formatDate(reg.created_at)"></td>
                                    <td class="p-4">
                                        <span :class="getStatusBadgeClass(reg.status)" class="px-3 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1 w-fit">
                                            <span x-html="getStatusIcon(reg.status)"></span>
                                            <span x-text="getStatusLabel(reg.status)"></span>
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <button 
                                            @click="selectedReg = reg"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-medium transition-colors border border-gray-200"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </template>
            </div>

            {{-- Pagination Controls --}}
            <template x-if="!isLoading && totalPages > 1">
                <div class="p-4 border-t border-gray-100 flex items-center justify-between bg-gray-50/30">
                    <span class="text-sm text-gray-600">
                        Halaman <span class="font-semibold text-gray-800" x-text="page"></span> dari <span class="font-semibold text-gray-800" x-text="totalPages"></span>
                    </span>
                    <div class="flex gap-2">
                        <button 
                            @click="page = Math.max(1, page - 1)"
                            :disabled="page === 1"
                            class="p-2 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button 
                            @click="page = Math.min(totalPages, page + 1)"
                            :disabled="page === totalPages"
                            class="p-2 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Detail Modal --}}
        <div 
            x-show="selectedReg" 
            style="display: none;"
            class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm"
            x-transition.opacity.duration.300ms
        >
            <div 
                @click.away="selectedReg = null"
                x-show="selectedReg"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="bg-white rounded-2xl shadow-xl w-full max-w-[800px] max-h-[90vh] overflow-hidden flex flex-col"
            >
                <template x-if="selectedReg">
                    <div class="flex flex-col h-full">
                        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50 shrink-0">
                            <h2 class="text-lg font-bold text-gray-800" x-text="'Detail Pendaftaran #' + selectedReg.id"></h2>
                            <button 
                                @click="selectedReg = null"
                                class="text-gray-400 hover:text-gray-600 hover:bg-gray-200 p-1.5 rounded-full transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        
                        <div class="p-6 overflow-y-auto flex-1">
                            <div class="flex justify-between items-start mb-6">
                                <div class="flex items-center gap-4">
                                    <template x-if="selectedReg.contingent?.image_url">
                                        <img :src="selectedReg.contingent.image_url" alt="Logo" class="w-16 h-16 rounded-lg object-cover border border-gray-200 bg-white" />
                                    </template>
                                    <template x-if="!selectedReg.contingent?.image_url">
                                        <div class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-200 font-bold text-xl" x-text="getInitials(selectedReg.contingent?.name)"></div>
                                    </template>
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900" x-text="selectedReg.contingent?.name || '-'"></h3>
                                        <p class="text-sm text-gray-500 font-medium mt-1">PIC: <span x-text="selectedReg.contingent?.pic?.name || '-'"></span> <span class="text-gray-400" x-text="'(' + (selectedReg.contingent?.pic?.email || '-') + ')'"></span></p>
                                    </div>
                                </div>
                                <span :class="getStatusBadgeClass(selectedReg.status)" class="px-3 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1 w-fit">
                                    <span x-html="getStatusIcon(selectedReg.status)"></span>
                                    <span x-text="getStatusLabel(selectedReg.status)"></span>
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-6 mb-8">
                                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                    <p class="text-xs text-gray-500 font-medium mb-1">Cabang Olahraga</p>
                                    <p class="font-semibold text-gray-800" x-text="selectedReg.sport?.name || '-'"></p>
                                    <p class="text-sm text-gray-600 mt-0.5">Kategori: <span x-text="selectedReg.sport_category?.name || '-'"></span></p>
                                    
                                    <div class="mt-3 flex gap-2 text-xs font-medium">
                                        <span class="bg-white px-2 py-1 rounded-md border border-gray-200 shadow-sm text-gray-700">
                                            Kapasitas Tim: <span x-text="selectedReg.max_members || '-'"></span>
                                        </span>
                                        <span class="bg-indigo-50 px-2 py-1 rounded-md border border-indigo-100 text-indigo-700">
                                            Terisi: <span x-text="selectedReg.current_members || '0'"></span>
                                        </span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                    <p class="text-xs text-gray-500 font-medium mb-1">Informasi Pengajuan</p>
                                    <p class="font-semibold text-gray-800">Status: <span class="capitalize" x-text="selectedReg.status"></span></p>
                                    <p class="text-sm text-gray-600 mt-0.5">Disubmit pada: <span x-text="formatDate(selectedReg.created_at)"></span></p>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    Daftar Pemain (<span x-text="(selectedReg.players || []).length"></span>)
                                </h4>
                                <div class="border border-gray-200 rounded-xl overflow-hidden overflow-y-auto max-h-[300px]">
                                    <table class="w-full text-left text-sm relative">
                                        <thead class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10 shadow-sm">
                                            <tr>
                                                <th class="px-4 py-3 font-medium text-gray-600 w-12 text-center">No</th>
                                                <th class="px-4 py-3 font-medium text-gray-600 w-16">Foto</th>
                                                <th class="px-4 py-3 font-medium text-gray-600">Pemain & Identitas</th>
                                                <th class="px-4 py-3 font-medium text-gray-600">Status / Unit</th>
                                                <th class="px-4 py-3 font-medium text-gray-600 text-center whitespace-nowrap">Status Risiko</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <template x-if="(selectedReg.players || []).length === 0">
                                                <tr>
                                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500 italic">Belum ada pemain terdaftar di tim ini.</td>
                                                </tr>
                                            </template>
                                            <template x-for="(player, idx) in selectedReg.players" :key="player.id">
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 text-center text-gray-500 align-top" x-text="idx + 1"></td>
                                                    <td class="px-4 py-3 align-top">
                                                        <template x-if="player.photo_path">
                                                            <img :src="player.photo_path" :alt="player.name" class="w-10 h-10 rounded-md object-cover border border-gray-200 bg-white" />
                                                        </template>
                                                        <template x-if="!player.photo_path">
                                                            <div class="w-10 h-10 rounded-md bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-200 font-bold" x-text="getInitials(player.name)"></div>
                                                        </template>
                                                    </td>
                                                    <td class="px-4 py-3 align-top">
                                                        <div class="font-medium text-gray-800" x-text="player.name"></div>
                                                        <div class="text-gray-500 text-xs mt-0.5" x-text="player.nim_nip || '-'"></div>
                                                    </td>
                                                    <td class="px-4 py-3 align-top">
                                                        <div class="text-gray-800 text-sm font-medium" x-text="player.employee_status || '-'"></div>
                                                        <div class="text-gray-500 text-xs mt-0.5 max-w-[150px] truncate" :title="player.work_location" x-text="player.work_location || '-'"></div>
                                                    </td>
                                                    <td class="px-4 py-3 text-center align-top">
                                                        <span :class="getRiskBadgeClass(player.risk_lvl)" class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold border">
                                                            <span x-text="getRiskLabel(player.risk_lvl)"></span>
                                                        </span>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 shrink-0">
                            <button 
                                @click="selectedReg = null"
                                class="px-5 py-2.5 rounded-xl font-medium text-gray-600 hover:bg-gray-200 transition-colors"
                            >
                                Tutup
                            </button>
                            
                            <template x-if="selectedReg.status === 'submitted'">
                                <div class="flex gap-3">
                                    <button 
                                        @click="actionModal = { type: 'reject', reg: selectedReg }"
                                        class="px-5 py-2.5 rounded-xl font-medium bg-red-100 text-red-700 hover:bg-red-200 transition-colors flex items-center gap-2"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Tolak Pendaftaran
                                    </button>
                                    <button 
                                        @click="actionModal = { type: 'verify', reg: selectedReg }"
                                        class="px-5 py-2.5 rounded-xl font-medium bg-green-600 text-white hover:bg-green-700 transition-colors flex items-center gap-2"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Verifikasi
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Confirmation Modal --}}
        <div 
            x-show="actionModal" 
            style="display: none;"
            class="fixed inset-0 bg-black/60 z-[60] flex items-center justify-center p-4 backdrop-blur-sm"
            x-transition.opacity.duration.300ms
        >
            <div 
                @click.away="actionModal = null"
                x-show="actionModal"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden p-6 text-center"
            >
                <template x-if="actionModal">
                    <div>
                        <div :class="actionModal.type === 'verify' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'" class="w-16 h-16 rounded-full mx-auto flex items-center justify-center mb-4">
                            <template x-if="actionModal.type === 'verify'">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </template>
                            <template x-if="actionModal.type !== 'verify'">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </template>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="actionModal.type === 'verify' ? 'Verifikasi Pendaftaran?' : 'Tolak Pendaftaran?'"></h3>
                        <p class="text-gray-500 mb-6">
                            Apakah Anda yakin ingin <span x-text="actionModal.type === 'verify' ? 'memverifikasi' : 'menolak'"></span> pendaftaran dari <span class="font-semibold text-gray-800" x-text="actionModal.reg?.contingent?.name"></span>?
                        </p>
                        
                        <div class="flex gap-3 justify-center">
                            <button 
                                @click="actionModal = null"
                                class="px-5 py-2.5 rounded-xl font-medium text-gray-600 hover:bg-gray-100 transition-colors"
                            >
                                Batal
                            </button>
                            <button 
                                @click="handleAction(actionModal.reg.id, actionModal.type)"
                                :class="actionModal.type === 'verify' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'"
                                class="px-5 py-2.5 rounded-xl font-medium text-white transition-colors flex items-center gap-2"
                            >
                                <span x-text="actionModal.type === 'verify' ? 'Ya, Verifikasi' : 'Ya, Tolak'"></span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Compliance Modal --}}
        <div 
            x-show="showCompliance" 
            style="display: none;"
            class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm"
            x-transition.opacity.duration.300ms
        >
            <div 
                @click.away="showCompliance = false"
                x-show="showCompliance"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col"
            >
                <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50 shrink-0">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Kepatuhan Pendaftaran Kontingen
                    </h2>
                    <button 
                        @click="showCompliance = false"
                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-200 p-1.5 rounded-full transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto flex-1 bg-gray-50/50">
                    <template x-if="isLoadingCompliance">
                        <div class="flex justify-center items-center h-48 text-gray-500">
                            <div class="w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                            <span class="ml-3 font-medium">Memuat data kepatuhan...</span>
                        </div>
                    </template>
                    
                    <template x-if="!isLoadingCompliance && complianceData.length > 0">
                        <div class="space-y-6">
                            <template x-for="(comp, idx) in complianceData" :key="idx">
                                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                                    <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between sm:items-center gap-4 bg-white">
                                        <div>
                                            <h3 class="font-bold text-gray-900 text-lg">
                                                <span x-text="comp.sport?.name"></span> 
                                                <template x-if="comp.sport_category?.name">
                                                    <span class="text-gray-500 text-sm font-normal" x-text="'(' + comp.sport_category.name + ')'"></span>
                                                </template>
                                            </h3>
                                            <div class="flex items-center gap-4 mt-2">
                                                <span class="text-xs font-medium text-gray-500" x-text="'Target: ' + comp.total_contingents + ' Kontingen'"></span>
                                                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded border border-green-100" x-text="'Terdaftar: ' + comp.registered_count"></span>
                                                <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-100" x-text="'Belum: ' + comp.not_registered_count"></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="text-right">
                                                <p class="text-xs text-gray-500 font-medium mb-1">Tingkat Kepatuhan</p>
                                                <p class="font-bold text-indigo-700" x-text="comp.compliance_rate + '%'"></p>
                                            </div>
                                            <div class="relative w-12 h-12">
                                                <svg class="w-12 h-12 transform -rotate-90">
                                                    <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4" fill="transparent" class="text-gray-100" />
                                                    <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4" fill="transparent" stroke-dasharray="125.6" :stroke-dashoffset="125.6 - (125.6 * comp.compliance_rate) / 100" class="text-indigo-600 transition-all duration-1000" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <template x-if="comp.not_registered && comp.not_registered.length > 0">
                                        <div class="p-5 bg-red-50/30">
                                            <h4 class="text-sm font-semibold text-red-800 mb-3 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                Kontingen Belum Mendaftar (<span x-text="comp.not_registered.length"></span>):
                                            </h4>
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="nr in comp.not_registered" :key="nr.id">
                                                    <span class="px-3 py-1.5 bg-white border border-red-200 text-red-700 text-xs font-medium rounded-lg shadow-sm" x-text="nr.name"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                    
                    <template x-if="!isLoadingCompliance && complianceData.length === 0">
                        <div class="text-center py-12 text-gray-500">
                            Data kepatuhan tidak tersedia.
                        </div>
                    </template>
                </div>

                <div class="p-5 border-t border-gray-100 bg-white flex justify-end shrink-0">
                    <button 
                        @click="showCompliance = false"
                        class="px-5 py-2.5 rounded-xl font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors"
                    >
                        Tutup Kepatuhan
                    </button>
                </div>
            </div>
        </div>

        {{-- Toast Notification --}}
        <div
            x-show="toast.show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            :class="{
                'bg-green-600': toast.type === 'success',
                'bg-red-600': toast.type === 'error',
                'bg-blue-600': toast.type === 'info',
            }"
            class="fixed bottom-6 left-1/2 transform -translate-x-1/2 text-white text-sm font-medium px-5 py-3 rounded-xl shadow-lg z-[9999] max-w-md text-center"
            style="display: none;"
            x-text="toast.message"
        ></div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('verifikasiTimManager', () => ({
            registrations: [],
            filterStatus: 'all',
            filterSport: '',
            searchQuery: '',
            selectedReg: null,
            actionModal: null, // { type: 'verify'|'reject', reg: null }
            
            isLoading: true,
            page: 1,
            totalPages: 1,
            
            showCompliance: false,
            complianceData: [],
            isLoadingCompliance: false,
            
            toast: { show: false, message: '', type: 'info' },

            init() {
                this.fetchRegistrations();
                this.$watch('filterStatus', () => { this.page = 1; this.fetchRegistrations(); });
                this.$watch('filterSport', () => { this.page = 1; this.fetchRegistrations(); });
                this.$watch('page', () => { this.fetchRegistrations(); });
            },

            get filteredData() {
                const query = this.searchQuery.toLowerCase();
                if (!query) return this.registrations;
                return this.registrations.filter(reg => {
                    return (reg.contingent?.name || '').toLowerCase().includes(query) ||
                           (reg.sport?.name || '').toLowerCase().includes(query);
                });
            },

            showToast(message, type = 'info') {
                this.toast = { show: true, message, type };
                setTimeout(() => { this.toast.show = false; }, 3500);
            },

            async api(method, url, data = null) {
                const opts = {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                };
                
                // Tambahkan CSRF Token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (csrfToken) {
                    opts.headers['X-CSRF-TOKEN'] = csrfToken;
                }
                
                if (data && method !== 'GET') opts.body = JSON.stringify(data);
                
                const res = await fetch(url, opts);
                const json = await res.json();
                
                if (!res.ok) throw new Error(json.message || 'Request failed');
                return json;
            },

            async fetchRegistrations() {
                this.isLoading = true;
                try {
                    let url = `/api/registrations?page=${this.page}`;
                    if (this.filterStatus !== 'all') {
                        url += `&status=${this.filterStatus}`;
                    }
                    if (this.filterSport) {
                        url += `&sport_id=${this.filterSport}`;
                    }
                    const res = await this.api('GET', url);
                    if (res.status === 'success') {
                        this.registrations = res.data.data || [];
                        this.totalPages = res.data.last_page || 1;
                    }
                } catch (e) {
                    console.error(e);
                    this.showToast('Gagal memuat data pendaftaran', 'error');
                } finally {
                    this.isLoading = false;
                }
            },

            async fetchCompliance() {
                this.isLoadingCompliance = true;
                this.showCompliance = true;
                try {
                    const res = await this.api('GET', '/api/registrations/compliance');
                    if (res.status === 'success') {
                        this.complianceData = res.data || [];
                    }
                } catch (e) {
                    console.error(e);
                    this.showToast('Gagal memuat data kepatuhan', 'error');
                } finally {
                    this.isLoadingCompliance = false;
                }
            },

            async handleAction(id, type) {
                try {
                    const status = type === 'verify' ? 'verified' : 'rejected';
                    const res = await this.api('POST', `/api/registrations/${id}/verify`, { status });
                    
                    if (res.status === 'success') {
                        this.showToast(`Pendaftaran berhasil di${type === 'verify' ? 'verifikasi' : 'tolak'}`, 'success');
                        this.fetchRegistrations();
                        this.actionModal = null;
                        this.selectedReg = null;
                    }
                } catch (e) {
                    console.error(e);
                    this.showToast(e.message || 'Gagal memperbarui status', 'error');
                }
            },

            formatDate(dateString) {
                if (!dateString) return '-';
                const d = new Date(dateString);
                return isNaN(d.getTime()) ? dateString : d.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
            },

            getInitials(name) {
                if (!name) return '?';
                return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
            },

            getStatusBadgeClass(status) {
                switch (status) {
                    case "submitted":
                    case "pending":
                        return "bg-yellow-100 text-yellow-800";
                    case "verified":
                        return "bg-green-100 text-green-800";
                    case "rejected":
                        return "bg-red-100 text-red-800";
                    case "draft":
                        return "bg-gray-100 text-gray-800";
                    default:
                        return "bg-gray-100 text-gray-800";
                }
            },

            getStatusLabel(status) {
                switch (status) {
                    case "submitted":
                    case "pending":
                        return "Pending";
                    case "verified":
                        return "Verified";
                    case "rejected":
                        return "Rejected";
                    case "draft":
                        return "Draft";
                    default:
                        return status;
                }
            },

            getStatusIcon(status) {
                switch (status) {
                    case "submitted":
                    case "pending":
                        return `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
                    case "verified":
                        return `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`;
                    case "rejected":
                        return `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`;
                    case "draft":
                        return `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`;
                    default:
                        return '';
                }
            },

            getRiskBadgeClass(risk) {
                if (!risk) return "bg-gray-100 text-gray-500 border-gray-200";
                const r = String(risk).toLowerCase();
                if (r.includes('high') || r.includes('tinggi')) return "bg-red-50 text-red-700 border-red-200";
                if (r.includes('medium') || r.includes('sedang')) return "bg-yellow-50 text-yellow-700 border-yellow-200";
                if (r.includes('low') || r.includes('rendah')) return "bg-green-50 text-green-700 border-green-200";
                return "bg-gray-100 text-gray-600 border-gray-200";
            },
            
            getRiskLabel(risk) {
                if (!risk) return "Belum Diketahui";
                const r = String(risk).toLowerCase();
                if (r.includes('high') || r.includes('tinggi')) return "Tinggi";
                if (r.includes('medium') || r.includes('sedang')) return "Sedang";
                if (r.includes('low') || r.includes('rendah')) return "Rendah";
                return risk;
            }
        }));
    });
    </script>
    @endpush
</x-layouts.dashboard>
