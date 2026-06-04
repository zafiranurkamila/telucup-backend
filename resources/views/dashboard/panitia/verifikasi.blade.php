<x-layouts.dashboard :roleLabel="'Super Admin'">
    <x-slot:title>Verifikasi Tim & Check-in</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-panitia')
    </x-slot:sidebar>

    <div x-data="verifikasiManager(@js($matchId))" class="space-y-6 pb-10">

        {{-- ==========================================================
             LOADING / ERROR STATES
             ========================================================== --}}
        
        {{-- Loading --}}
        <template x-if="isLoading">
            <div class="flex min-h-[60vh] items-center justify-center">
                <div class="flex flex-col items-center gap-3 text-gray-500">
                    <div class="h-8 w-8 animate-spin rounded-full border-4 border-gray-200 border-t-brand"></div>
                    <p class="text-sm font-medium">Memuat data pertandingan...</p>
                </div>
            </div>
        </template>

        {{-- Error / No Match --}}
        <template x-if="!isLoading && (error || !matchData)">
            <div class="flex min-h-[60vh] items-center justify-center p-6">
                <div class="w-full max-w-md rounded-2xl border border-red-100 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-50">
                        <svg class="h-7 w-7 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h2 class="mb-2 text-lg font-bold text-gray-900">Pertandingan Tidak Ditemukan</h2>
                    <p class="mb-6 text-sm text-gray-500" x-text="error || 'Pilih pertandingan dari halaman bagan terlebih dahulu.'"></p>
                    <a href="javascript:history.back()" class="inline-flex items-center gap-2 rounded-lg bg-brand px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-hover">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali ke Bagan
                    </a>
                </div>
            </div>
        </template>

        {{-- ==========================================================
             MAIN CONTENT
             ========================================================== --}}
        <template x-if="!isLoading && matchData && !error">
            <div class="space-y-5">
                {{-- Top Navigation Bar --}}
                <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                    <a href="javascript:history.back()" class="flex items-center gap-1.5 text-sm font-semibold text-gray-600 hover:text-gray-900 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali ke Bagan
                    </a>
                    <button @click="fetchData(true)" :disabled="isRefreshing" class="flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-50 transition">
                        <svg :class="isRefreshing ? 'animate-spin' : ''" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Refresh
                    </button>
                </div>

                <div class="grid gap-5 xl:grid-cols-[1fr_280px]">
                    {{-- LEFT COLUMN --}}
                    <div class="min-w-0 space-y-4">
                        
                        {{-- Header Card --}}
                        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                            <p class="mb-4 text-xs font-bold text-gray-400 uppercase tracking-wider" x-text="matchData.round_name + ' • Match #' + matchData.match_number"></p>
                            
                            <div class="flex flex-wrap items-center gap-5">
                                {{-- Team A --}}
                                <div class="flex flex-1 min-w-0 items-center gap-3">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand text-sm font-black text-white shadow-md" x-text="getInitials(matchData.team_a?.contingent?.name || '?')"></div>
                                    <div class="min-w-0">
                                        <p class="font-black text-gray-900 leading-tight truncate" x-text="matchData.team_a?.contingent?.name || 'TBD'"></p>
                                        <p class="text-xs text-gray-400">
                                            <span x-text="matchData.team_a?.players.filter(p => p.checked_in).length || 0"></span>/
                                            <span x-text="matchData.team_a?.players.length || 0"></span> hadir
                                        </p>
                                    </div>
                                </div>

                                <div class="shrink-0 rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-black text-gray-500">VS</div>

                                {{-- Team B --}}
                                <div class="flex flex-1 min-w-0 items-center gap-3">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-sm font-black text-white shadow-md" x-text="getInitials(matchData.team_b?.contingent?.name || '?')"></div>
                                    <div class="min-w-0">
                                        <p class="font-black text-gray-900 leading-tight truncate" x-text="matchData.team_b?.contingent?.name || 'TBD'"></p>
                                        <p class="text-xs text-gray-400">
                                            <span x-text="matchData.team_b?.players.filter(p => p.checked_in).length || 0"></span>/
                                            <span x-text="matchData.team_b?.players.length || 0"></span> hadir
                                        </p>
                                    </div>
                                </div>

                                <div class="hidden lg:block h-14 w-px bg-gray-100"></div>

                                {{-- Status --}}
                                <div class="shrink-0 text-right">
                                    <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">STATUS MATCH</p>
                                    <span :class="statusInfo.color" class="inline-block rounded-full px-3 py-1 text-xs font-black" x-text="statusInfo.label"></span>
                                    <template x-if="!allCheckedIn && matchData.status === 'scheduled'">
                                        <p class="mt-1.5 text-[10px] text-gray-400 max-w-[160px]">Check-in semua pemain untuk melanjutkan</p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                                <div class="mb-2 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Total Pemain</p>
                                <p class="mt-0.5 text-2xl font-black text-gray-900" x-text="totalPlayers"></p>
                            </div>
                            
                            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                                <div class="mb-2 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-green-50 text-green-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Sudah Hadir</p>
                                <p class="mt-0.5 text-2xl font-black text-gray-900" x-text="checkedInCount"></p>
                            </div>

                            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                                <div class="mb-2 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-orange-50 text-orange-500">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Belum Hadir</p>
                                <p class="mt-0.5 text-2xl font-black text-gray-900" x-text="notCheckedInCount"></p>
                            </div>

                            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                                <div :class="highRiskCount > 0 ? 'bg-red-50 text-brand' : 'bg-gray-50 text-gray-500'" class="mb-2 inline-flex h-9 w-9 items-center justify-center rounded-xl">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                </div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">High Risk</p>
                                <p class="mt-0.5 text-2xl font-black text-gray-900" x-text="highRiskCount"></p>
                            </div>
                        </div>

                        {{-- Player Tables --}}
                        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                            <div class="flex border-b border-gray-100">
                                <button @click="activeTab = 'a'" :class="activeTab === 'a' ? 'border-brand text-brand' : 'border-transparent text-gray-500 hover:text-gray-700'" class="flex-1 py-3.5 px-4 text-sm font-bold transition border-b-2" x-text="matchData.team_a?.contingent?.name || 'Tim A'"></button>
                                <button @click="activeTab = 'b'" :class="activeTab === 'b' ? 'border-brand text-brand' : 'border-transparent text-gray-500 hover:text-gray-700'" class="flex-1 py-3.5 px-4 text-sm font-bold transition border-b-2" x-text="matchData.team_b?.contingent?.name || 'Tim B'"></button>
                            </div>

                            {{-- Check-in All Button --}}
                            <template x-if="activeTeam && matchData?.status === 'scheduled'">
                                <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 border-b border-gray-100">
                                    <button
                                        @click="checkinAll(activeTeam)"
                                        :disabled="activeTeamNotIn === 0 || isCheckingInAll"
                                        class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-bold text-gray-700 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
                                    >
                                        <template x-if="isCheckingInAll">
                                            <svg class="h-3.5 w-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        </template>
                                        <template x-if="!isCheckingInAll">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12l-3 3-3-3"/></svg>
                                        </template>
                                        <span x-text="'Check-in Semua Tim ' + (activeTeam.contingent?.name?.split(' ')[0] || '')"></span>
                                    </button>
                                    <template x-if="activeTeamNotIn > 0">
                                        <span class="flex items-center gap-1.5 text-xs font-semibold text-red-500">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span x-text="'Belum hadir semua (' + activeTeamNotIn + ')'"></span>
                                        </span>
                                    </template>
                                </div>
                            </template>

                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[640px] text-sm">
                                    <thead>
                                        <tr class="bg-gray-50 text-[11px] font-bold uppercase tracking-wide text-gray-400">
                                            <th class="w-10 px-4 py-3 text-left">No.</th>
                                            <th class="px-4 py-3 text-left">Nama Pemain</th>
                                            <th class="px-4 py-3 text-left">NIM</th>
                                            <th class="px-4 py-3 text-left">Check-in</th>
                                            <th class="px-4 py-3 text-left">Status Risiko</th>
                                            <th class="px-4 py-3 text-left">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <template x-if="!activeTeam || activeTeam.players.length === 0">
                                            <tr>
                                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400">
                                                    Tim ini belum memiliki pemain terdaftar.
                                                </td>
                                            </tr>
                                        </template>

                                        <template x-for="(player, idx) in (activeTeam?.players || [])" :key="player.id">
                                            <tr :class="player.checked_in ? 'bg-green-50/40' : ''" class="transition hover:bg-gray-50/60">
                                                <td class="px-4 py-3.5 font-bold text-gray-400" x-text="idx + 1"></td>
                                                <td class="px-4 py-3.5">
                                                    <div class="flex items-center gap-3">
                                                        <div :class="(player.risk_lvl || '').toLowerCase() === 'high' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-black overflow-hidden">
                                                            <template x-if="player.photo_path">
                                                                <img :src="player.photo_path" :alt="player.name" class="h-8 w-8 object-cover" />
                                                            </template>
                                                            <template x-if="!player.photo_path">
                                                                <span x-text="getInitials(player.name)"></span>
                                                            </template>
                                                        </div>
                                                        <div>
                                                            <p class="font-bold text-gray-900 leading-tight" x-text="player.name"></p>
                                                            <p class="text-[11px] text-gray-400" x-text="player.nim_nip || '-'"></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3.5 font-mono text-xs text-gray-500" x-text="player.nim_nip || '-'"></td>
                                                <td class="px-4 py-3.5">
                                                    <template x-if="player.checked_in">
                                                        <div>
                                                            <span class="flex items-center gap-1 text-xs font-bold text-green-600">
                                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                                Checked-in
                                                            </span>
                                                            <span class="text-[10px] text-gray-400" x-text="formatTime(player.checked_in_at)"></span>
                                                        </div>
                                                    </template>
                                                    <template x-if="!player.checked_in">
                                                        <button 
                                                            @click.prevent="toggleCheckin(player.id, player.checked_in, player.name)" 
                                                            :disabled="loadingPlayers[player.id] === true"
                                                            class="flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-bold text-brand hover:bg-red-100 disabled:opacity-50 transition"
                                                        >
                                                            <svg x-show="loadingPlayers[player.id] === true" style="display: none;" class="h-3 w-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                            <svg x-show="loadingPlayers[player.id] !== true" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            Check-in
                                                        </button>
                                                    </template>
                                                </td>
                                                <td class="px-4 py-3.5">
                                                    <span :class="getRiskBadgeClass(player.risk_lvl)" class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-black w-max" x-text="(player.risk_lvl || 'LAYAK').toUpperCase()"></span>
                                                </td>
                                                <td class="px-4 py-3.5">
                                                    <div class="flex items-center gap-2">
                                                        <template x-if="player.checked_in">
                                                            <button 
                                                                @click.prevent="toggleCheckin(player.id, player.checked_in, player.name)" 
                                                                :disabled="loadingPlayers[player.id] === true"
                                                                title="Batalkan check-in"
                                                                class="rounded-lg border border-gray-200 bg-white p-1.5 text-gray-400 hover:text-red-500 hover:border-red-200 disabled:opacity-50 transition"
                                                            >
                                                                <svg x-show="loadingPlayers[player.id] === true" style="display: none;" class="h-4 w-4 animate-spin text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                                <svg x-show="loadingPlayers[player.id] !== true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                            </button>
                                                        </template>
                                                        <button class="flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2 py-1 text-xs font-semibold text-gray-500 hover:bg-gray-50 transition">
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            Lihat
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Footer Actions --}}
                        <template x-if="matchData?.status === 'scheduled'">
                            <div class="flex items-center justify-between rounded-2xl border border-gray-100 bg-white px-5 py-3 shadow-sm">
                                <p class="text-xs text-gray-400 font-medium">
                                    <span x-text="checkedInCount"></span> / <span x-text="totalPlayers"></span> pemain sudah check-in
                                </p>
                                <div class="flex items-center gap-3">
                                    <button
                                        @click="startMatch()"
                                        :disabled="!allCheckedIn || isStarting"
                                        :class="allCheckedIn ? 'bg-emerald-500 text-white hover:bg-emerald-600 shadow-sm' : 'bg-gray-100 border border-gray-200 text-gray-400 cursor-not-allowed'"
                                        class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-bold transition"
                                    >
                                        <template x-if="isStarting">
                                            <svg class="h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        </template>
                                        <template x-if="!isStarting">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                        </template>
                                        <span x-text="isStarting ? 'Memulai...' : 'Mulai Pertandingan'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>

                    </div>

                    {{-- RIGHT COLUMN --}}
                    <div class="space-y-4">
                        {{-- Ringkasan --}}
                        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                            <div class="border-b border-gray-50 px-4 py-3.5">
                                <h2 class="text-sm font-black text-gray-900">Ringkasan Match</h2>
                            </div>
                            <div class="space-y-3.5 p-4 text-sm">
                                <div class="flex items-start gap-2.5">
                                    <div class="mt-0.5 shrink-0"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Jadwal</p>
                                        <div class="mt-0.5 font-semibold text-gray-800" x-text="matchData.match_date ? (matchData.match_date + ' ' + (matchData.match_time || '')) : 'Belum Dijadwalkan'"></div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <div class="mt-0.5 shrink-0"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Lapangan</p>
                                        <div class="mt-0.5 font-semibold text-gray-800" x-text="matchData.location || '-'"></div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <div class="mt-0.5 shrink-0"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg></div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Kategori</p>
                                        <div class="mt-0.5 font-semibold text-gray-800" x-text="matchData.round_name"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </template>
        
        {{-- Toast --}}
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
    Alpine.data('verifikasiManager', (initialMatchId) => ({
        matchId: initialMatchId,
        matchData: null,
        isLoading: true,
        isRefreshing: false,
        error: null,
        activeTab: 'a',
        loadingPlayers: {},
        isStarting: false,
        isCheckingInAll: false,
        toast: { show: false, message: '', type: 'info' },

        init() {
            if (!this.matchId) {
                this.error = "ID Pertandingan tidak diberikan.";
                this.isLoading = false;
                return;
            }
            this.fetchData();
        },

        get activeTeam() {
            return this.activeTab === 'a' ? this.matchData?.team_a : this.matchData?.team_b;
        },

        get inactiveTeam() {
            return this.activeTab === 'a' ? this.matchData?.team_b : this.matchData?.team_a;
        },

        get activeTeamNotIn() {
            return this.activeTeam?.players?.filter(p => !p.checked_in).length || 0;
        },

        get totalPlayers() {
            const a = this.matchData?.team_a?.players?.length || 0;
            const b = this.matchData?.team_b?.players?.length || 0;
            return a + b;
        },

        get checkedInCount() {
            const a = this.matchData?.team_a?.players?.filter(p => p.checked_in).length || 0;
            const b = this.matchData?.team_b?.players?.filter(p => p.checked_in).length || 0;
            return a + b;
        },

        get notCheckedInCount() {
            return this.totalPlayers - this.checkedInCount;
        },

        get highRiskCount() {
            const a = this.matchData?.team_a?.players?.filter(p => (p.risk_lvl||'').toLowerCase() === 'high').length || 0;
            const b = this.matchData?.team_b?.players?.filter(p => (p.risk_lvl||'').toLowerCase() === 'high').length || 0;
            return a + b;
        },

        get allCheckedIn() {
            return this.totalPlayers > 0 && this.checkedInCount === this.totalPlayers;
        },

        get statusInfo() {
            if (!this.matchData) return { label: 'LOADING...', color: 'bg-gray-100 text-gray-500' };
            switch (this.matchData.status) {
                case "live": return { label: "MATCH STARTED", color: "bg-green-100 text-green-700" };
                case "finished": return { label: "FINISHED", color: "bg-gray-200 text-gray-600" };
                default: return { label: "WAITING CHECK-IN", color: "bg-amber-100 text-amber-700" };
            }
        },

        getInitials(name) {
            return name.split(" ").slice(0, 2).map(w => w[0]).join("").toUpperCase();
        },

        formatTime(isoString) {
            if (!isoString) return "-";
            const d = new Date(isoString);
            return d.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" }) + " WIB";
        },

        getRiskBadgeClass(level) {
            const l = (level || '').toLowerCase();
            if (l === 'high') return 'bg-red-100 text-red-700';
            if (l === 'medium') return 'bg-amber-100 text-amber-700';
            if (!l || l === 'low') return 'bg-green-100 text-green-700';
            return 'bg-gray-100 text-gray-500';
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            };
            if (data && method !== 'GET') opts.body = JSON.stringify(data);
            const res = await fetch('/dashboard/panitia' + url, opts);
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Request failed');
            return json;
        },

        async fetchData(silent = false) {
            if (!silent) this.isLoading = true;
            else this.isRefreshing = true;

            try {
                // We fetch both match details and checkin status
                // The API /matches/{id}/checkin returns both Game data and Player Checkins
                const res = await this.api('GET', `/matches/${this.matchId}/checkin`);
                this.matchData = res.data;
                this.error = null;
            } catch (e) {
                this.error = e.message || "Gagal mengambil data.";
            } finally {
                this.isLoading = false;
                this.isRefreshing = false;
            }
        },

        async toggleCheckin(playerId, isCheckedIn, playerName) {
            if (this.loadingPlayers[playerId]) return;
            this.loadingPlayers = { ...this.loadingPlayers, [playerId]: true };

            try {
                if (isCheckedIn) {
                    await this.api('DELETE', `/matches/${this.matchId}/checkin/${playerId}`);
                    this.showToast(`${playerName} batal check-in.`, 'info');
                } else {
                    await this.api('POST', `/matches/${this.matchId}/checkin/${playerId}`);
                    this.showToast(`${playerName} berhasil check-in.`, 'success');
                }
                await this.fetchData(true);
            } catch (e) {
                console.error("Toggle Checkin Error:", e);
                this.showToast(e.message || "Gagal mengubah check-in.", "error");
            } finally {
                this.loadingPlayers = { ...this.loadingPlayers, [playerId]: false };
            }
        },

        async checkinAll(team) {
            if (!team || !team.players) return;
            const notIn = team.players.filter(p => !p.checked_in);
            if (notIn.length === 0) return;

            this.isCheckingInAll = true;
            try {
                const promises = notIn.map(p => this.api('POST', `/matches/${this.matchId}/checkin/${p.id}`).catch(e => null));
                await Promise.all(promises);
                this.showToast(`Berhasil memproses check-in tim.`, 'success');
                await this.fetchData(true);
            } catch (e) {
                this.showToast("Gagal melakukan check-in semua pemain.", "error");
            } finally {
                this.isCheckingInAll = false;
            }
        },

        async startMatch() {
            if (!this.allCheckedIn || this.isStarting) return;
            this.isStarting = true;

            try {
                await this.api('PATCH', `/matches/${this.matchId}/status`, { status: 'live' });
                this.showToast('Pertandingan berhasil dimulai!', 'success');
                await this.fetchData(true);
            } catch (e) {
                this.showToast(e.message || "Gagal memulai pertandingan.", "error");
            } finally {
                this.isStarting = false;
            }
        }
    }));
});
</script>
@endpush
</x-layouts.dashboard>
