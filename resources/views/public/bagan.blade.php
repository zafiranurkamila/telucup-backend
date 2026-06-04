<x-layout-public>
    <x-slot:title>Kelola Bagan</x-slot:title>

    

    {{-- Alpine.js state container --}}
    <div
        x-data="bracketManager(@js($sports))"
        class="space-y-6 pb-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto"
    >
        {{-- Header --}}
        <div class="text-center mb-6 pt-4">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-gray-100 shadow-sm text-xs font-bold text-brand uppercase tracking-widest mb-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                TURNAMEN TAHUNAN
            </div>
            <h1 class="text-3xl md:text-5xl font-black text-gray-900 tracking-tight mb-4">
                Bagan <span class="text-brand">Tel-U Cup</span>
            </h1>
            <p class="text-gray-500 max-w-2xl mx-auto text-sm md:text-base">
                Jelajahi peta persaingan olahraga terbesar di kampus. Pantau tim favorit Anda dari babak penyisihan hingga mencapai puncak kejayaan di Grand Final.
            </p>
        </div>

        {{-- ============================================================
             Filter Bar
             ============================================================ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 md:p-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                {{-- Sport select --}}
                <div class="md:col-span-5">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Cabang Olahraga</label>
                    <div class="relative">
                        <select
                            x-model="selectedSportId"
                            @change="onSportChange()"
                            class="w-full appearance-none bg-white border border-gray-200 rounded-lg py-2.5 pl-4 pr-10 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-colors"
                        >
                            <option value="" disabled>Pilih cabang olahraga...</option>
                            @foreach($sports as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Category select --}}
                <div class="md:col-span-4">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Sub-Kategori</label>
                    <div class="relative">
                        <select
                            x-model="selectedCategoryId"
                            :disabled="!hasCategories"
                            x-html="categoryOptionsHtml"
                            class="w-full appearance-none bg-white border border-gray-200 rounded-lg py-2.5 pl-4 pr-10 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-colors disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed"
                        >
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Cari button --}}
                <div class="md:col-span-3 flex flex-col items-start md:items-center justify-end">
                    <button 
                        @click="handleSearch()"
                        :disabled="!isReady"
                        class="w-full bg-[#b6252a] text-white py-2.5 px-4 rounded-lg font-bold hover:bg-[#9a1e22] transition-colors disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Cari
                    </button>
                </div>
            </div>

            
        </div>

        {{-- ============================================================
             Bracket Board
             ============================================================ --}}

        {{-- Empty state --}}
        <template x-if="!bracketData && !selectedSportId">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-700 mb-2">Pilih Cabang Olahraga</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto">Pilih cabang olahraga dari filter di atas untuk melihat atau membuat bagan pertandingan.</p>
            </div>
        </template>

        {{-- Loading --}}
        <template x-if="isLoading">
            <div class="flex items-center justify-center py-16">
                <div class="h-8 w-8 animate-spin rounded-full border-4 border-gray-200 border-t-brand"></div>
                <span class="ml-3 text-sm text-gray-500">Memuat bagan...</span>
            </div>
        </template>

        

        {{-- Bracket rounds --}}
        <template x-if="bracketData && !isLoading">
            <div class="space-y-6">
                <style>
                    /* Tournament Bracket CSS */
                    .bracket-container {
                        display: flex;
                        gap: 40px; /* Space for the 40px connector (20px left + 20px right) */
                    }
                    
                    .bracket-round-col {
                        display: flex;
                        flex-direction: column;
                        width: 280px;
                        flex-shrink: 0;
                        position: relative;
                    }

                    .bracket-round {
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        flex: 1 1 auto;
                        position: relative;
                    }
                    /* Force stretch on direct children */
                    .bracket-round > * {
                        flex: 1 1 0%;
                    }

                    .match-wrapper {
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        position: relative;
                        padding: 12px 0;
                        width: 100%;
                    }

                    .match-wrapper::after, .match-wrapper::before {
                        content: '';
                        position: absolute;
                        border-color: #cbd5e1;
                        z-index: 0;
                    }

                    /* Connectors pointing RIGHT (out from left round) */
                    .match-wrapper:not(.is-last-round)::after {
                        right: -21px;
                        width: 21px;
                        border-right-width: 2px;
                        border-right-style: solid;
                    }
                    
                    /* Top match (odd): line goes DOWN */
                    .match-wrapper:not(.is-last-round).is-top-match::after {
                        top: 50%;
                        height: 50%;
                        border-top-width: 2px;
                        border-top-style: solid;
                        border-right-width: 2px;
                        border-right-style: solid;
                    }

                    /* Bottom match (even): line goes UP */
                    .match-wrapper:not(.is-last-round).is-bottom-match::after {
                        top: 0;
                        height: 50%;
                        border-bottom-width: 2px;
                        border-bottom-style: solid;
                        border-right-width: 2px;
                        border-right-style: solid;
                    }
                    
                    /* Bye handling: if a top match doesn't have a bottom pair */
                    .match-wrapper:not(.is-last-round).is-bye-match::after {
                        height: 0;
                        border-right: none;
                    }

                    /* Connectors pointing LEFT (in to right round) */
                    .bracket-round-col:not(:first-child) .match-wrapper::before {
                        left: -20px;
                        top: 50%;
                        width: 21px;
                        border-top-width: 2px;
                        border-top-style: solid;
                    }
</style>
                {{-- Legend --}}
                <div class="flex items-center gap-4 text-xs font-medium text-gray-500 mb-6 px-6">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-brand"></span> Selesai</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-400"></span> Sedang Bermain</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-gray-300"></span> Menunggu</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-gray-100"></span> Bye</span>
                </div>

                {{-- Scrollable bracket container --}}
                <div class="overflow-x-auto overflow-y-hidden -mx-6 px-6 pb-4">
                    <div class="bracket-container" :style="'min-width: fit-content; padding-top: 20px; padding-bottom: ' + (bracketData.third_place_match ? '260px' : '20px') + ';'">
                        <template x-for="(round, rIndex) in bracketData.rounds" :key="round.round">
                            <div class="bracket-round-col">
                                
                                <!-- Header Ronde Normal -->
                                <template x-if="rIndex !== bracketData.rounds.length - 1">
                                    <div class="h-20 shrink-0 flex flex-col items-center justify-end pb-6 text-center">
                                        <span class="inline-block bg-white border border-gray-200 text-gray-700 text-[11px] font-extrabold px-4 py-1.5 rounded-full uppercase tracking-widest shadow-sm" x-text="round.name"></span>
                                        <div class="text-[9px] text-gray-400 mt-1 font-semibold uppercase" x-text="(round.matches.length * 2) + ' TEAMS'"></div>
                                    </div>
                                </template>

                                <!-- Header Grand Finals -->
                                <template x-if="rIndex === bracketData.rounds.length - 1">
                                    <div class="h-20 shrink-0 flex flex-col items-center justify-end pb-6 text-center">
                                        <span class="inline-flex items-center gap-1.5 bg-[#a81d22] text-white text-sm font-black px-5 py-2 rounded-full uppercase tracking-widest shadow-md">
                                            🏆 GRAND FINALS
                                        </span>
                                    </div>
                                </template>

                                <div class="bracket-round">
                                    <template x-for="(match, mIndex) in round.matches" :key="match.id">
                                        <div class="match-wrapper" :class="{
                                            'is-last-round': rIndex === bracketData.rounds.length - 1,
                                            'is-top-match': mIndex % 2 === 0,
                                            'is-bottom-match': mIndex % 2 === 1,
                                            'is-bye-match': (mIndex % 2 === 0) && (mIndex === round.matches.length - 1)
                                        }">
                                            <div class="relative w-full">
                                                <!-- Background highlight untuk Grand Finals -->
                                                <template x-if="rIndex === bracketData.rounds.length - 1">
                                                    <div class="absolute inset-0 bg-red-50/50 border-2 border-red-100 rounded-3xl -m-4 pointer-events-none z-0">
                                                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-white border border-red-100 text-red-600 text-[9px] font-bold px-3 py-1 rounded-full uppercase tracking-wider whitespace-nowrap">CHAMPIONSHIP ARENA</div>
                                                    </div>
                                                </template>

                                                <div
                                                    :class="{
                                                        'opacity-50': match.status === 'bye',
                                                        'hover:shadow-md transition-all': match.status !== 'bye',
                                                    }"
                                                    class="bg-white rounded-xl border border-gray-200 shadow-sm transition-all duration-150 overflow-hidden relative z-10"
                                                >
                                                    {{-- Status bar --}}
                                                    <div class="flex items-center justify-between px-3 py-1.5 border-b border-gray-100 bg-gray-50/50">
                                                        <span class="text-[10px] font-bold text-gray-400 uppercase flex items-center gap-1">
                                                            <svg class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                            <span x-text="'MATCH #' + match.match_number"></span>
                                                        </span>
                                                        <span
                                                            :class="{
                                                                'text-gray-500': match.status === 'scheduled',
                                                                'text-red-600 flex items-center gap-1': match.status === 'live',
                                                                'text-gray-900': match.status === 'finished',
                                                                'text-gray-400': match.status === 'bye',
                                                            }"
                                                            class="text-[10px] font-bold uppercase"
                                                        >
                                                            <template x-if="match.status === 'live'">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-pulse"></span>
                                                            </template>
                                                            <span x-text="match.status === 'finished' ? 'SELESAI' : match.status"></span>
                                                        </span>
                                                    </div>

                                                    {{-- Team A --}}
                                                    <div
                                                        :class="{
                                                            'bg-brand/5': match.winner && match.winner.registration_id === match.team_a?.registration_id,
                                                        }"
                                                        class="px-3 py-2.5 flex items-center justify-between"
                                                    >
                                                        <div class="flex items-center gap-2 overflow-hidden pointer-events-none">
                                                            <img :src="match.team_a?.contingent?.logo_url || 'https://ui-avatars.com/api/?name=' + (match.team_a?.contingent?.name || 'A') + '&background=f3f4f6&color=9ca3af'" class="w-6 h-6 rounded-full object-cover shrink-0">
                                                            <span class="text-sm font-bold text-gray-800 truncate" x-text="match.team_a?.contingent?.name ?? 'TBD'"></span>
                                                        </div>
                                                        <span :class="{'text-brand': match.winner && match.winner.registration_id === match.team_a?.registration_id, 'text-gray-900': !match.winner}" class="text-sm font-black ml-2 tabular-nums" x-text="match.score_a ?? ''"></span>
                                                    </div>

                                                    <div class="border-t border-gray-100 mx-3"></div>

                                                    {{-- Team B --}}
                                                    <div
                                                        :class="{
                                                            'bg-brand/5': match.winner && match.winner.registration_id === match.team_b?.registration_id,
                                                        }"
                                                        class="px-3 py-2.5 flex items-center justify-between"
                                                    >
                                                        <div class="flex items-center gap-2 overflow-hidden pointer-events-none">
                                                            <img :src="match.team_b?.contingent?.logo_url || 'https://ui-avatars.com/api/?name=' + (match.team_b?.contingent?.name || 'B') + '&background=f3f4f6&color=9ca3af'" class="w-6 h-6 rounded-full object-cover shrink-0">
                                                            <span class="text-sm font-bold text-gray-800 truncate" x-text="match.team_b?.contingent?.name ?? 'TBD'"></span>
                                                        </div>
                                                        <span :class="{'text-brand': match.winner && match.winner.registration_id === match.team_b?.registration_id, 'text-gray-900': !match.winner}" class="text-sm font-black ml-2 tabular-nums" x-text="match.score_b ?? ''"></span>
                                                    </div>

                                                    {{-- Schedule info --}}
                                                    <div class="px-3 py-2 border-t border-gray-100 bg-white flex items-center justify-between mt-1">
                                                        <div class="flex items-center text-[10px] text-gray-500 font-medium">
                                                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                            <span x-text="match.match_date ? (new Date(match.match_date).toLocaleDateString('id-ID', {day:'numeric', month:'short'})) : 'TBD'"></span>
                                                            <template x-if="match.match_time"><span class="ml-1" x-text="match.match_time"></span></template>
                                                        </div>
                                                        <div class="text-[9px] font-bold text-gray-800 hover:text-brand transition-colors uppercase cursor-pointer">
                                                            Detail Pertandingan &gt;
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                
                                <!-- Juara 3 (Ditampilkan di bawah Grand Final di kolom terakhir) -->
                                <template x-if="rIndex === bracketData.rounds.length - 1 && bracketData.third_place_match">
                                    <div class="absolute top-full left-0 w-full mt-12">
                                        <div class="text-center mb-6">
                                            <span class="inline-block bg-[#a81d22] text-white text-[11px] font-bold px-8 py-2 rounded-full uppercase tracking-wider shadow-sm">JUARA 3</span>
                                        </div>
                                        <div class="match-wrapper is-last-round pt-0 pb-0 relative z-10">
                                            <div
                                                class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-150 overflow-hidden"
                                            >
                                                <div class="flex items-center justify-between px-3 py-1.5 border-b border-gray-100 bg-gray-50/50">
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase flex items-center gap-1">
                                                        <svg class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                        <span x-text="'MATCH #' + bracketData.third_place_match.match_number"></span>
                                                    </span>
                                                    <span class="text-[10px] font-bold text-gray-500 uppercase" x-text="bracketData.third_place_match.status === 'finished' ? 'SELESAI' : bracketData.third_place_match.status"></span>
                                                </div>
                                                <div class="px-3 py-2.5 flex items-center justify-between"
                                                >
                                                    <div class="flex items-center gap-2 overflow-hidden pointer-events-none">
                                                        <img :src="bracketData.third_place_match.team_a?.contingent?.logo_url || 'https://ui-avatars.com/api/?name=' + (bracketData.third_place_match.team_a?.contingent?.name || 'A') + '&background=f3f4f6&color=9ca3af'" class="w-6 h-6 rounded-full object-cover shrink-0">
                                                        <span class="text-sm font-bold text-gray-800 truncate" x-text="bracketData.third_place_match.team_a?.contingent?.name ?? 'TBD'"></span>
                                                    </div>
                                                    <span class="text-sm font-black ml-2 tabular-nums" x-text="bracketData.third_place_match.score_a ?? ''"></span>
                                                </div>
                                                <div class="border-t border-gray-100 mx-3"></div>
                                                <div class="px-3 py-2.5 flex items-center justify-between"
                                                >
                                                    <div class="flex items-center gap-2 overflow-hidden pointer-events-none">
                                                        <img :src="bracketData.third_place_match.team_b?.contingent?.logo_url || 'https://ui-avatars.com/api/?name=' + (bracketData.third_place_match.team_b?.contingent?.name || 'B') + '&background=f3f4f6&color=9ca3af'" class="w-6 h-6 rounded-full object-cover shrink-0">
                                                        <span class="text-sm font-bold text-gray-800 truncate" x-text="bracketData.third_place_match.team_b?.contingent?.name ?? 'TBD'"></span>
                                                    </div>
                                                    <span class="text-sm font-black ml-2 tabular-nums" x-text="bracketData.third_place_match.score_b ?? ''"></span>
                                                </div>
                                                <div class="px-3 py-2 border-t border-gray-100 bg-white flex items-center justify-between mt-1">
                                                    <div class="flex items-center text-[10px] text-gray-500 font-medium">
                                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                        <span x-text="bracketData.third_place_match.match_date ? (new Date(bracketData.third_place_match.match_date).toLocaleDateString('id-ID', {day:'numeric', month:'short'})) : 'TBD'"></span>
                                                    </div>
                                                    <div class="text-[9px] font-bold text-gray-800 hover:text-brand transition-colors uppercase cursor-pointer">
                                                        Detail Pertandingan &gt;
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </template>
                    </div>
                </div>

                {{-- Results (Champions) --}}
                <template x-if="bracketData.results && bracketData.results.juara1">
                    <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border border-yellow-200 rounded-xl p-5">
                        <h3 class="font-bold text-yellow-800 mb-3 flex items-center gap-2">🏆 Hasil Turnamen</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <template x-if="bracketData.results.juara1">
                                <div class="text-center">
                                    <div class="text-2xl mb-1">🥇</div>
                                    <div class="font-bold text-gray-900" x-text="bracketData.results.juara1.contingent?.name ?? '-'"></div>
                                    <div class="text-xs text-gray-500">Juara 1</div>
                                </div>
                            </template>
                            <template x-if="bracketData.results.juara2">
                                <div class="text-center">
                                    <div class="text-2xl mb-1">🥈</div>
                                    <div class="font-bold text-gray-900" x-text="bracketData.results.juara2.contingent?.name ?? '-'"></div>
                                    <div class="text-xs text-gray-500">Juara 2</div>
                                </div>
                            </template>
                            <template x-if="bracketData.results.juara3">
                                <div class="text-center">
                                    <div class="text-2xl mb-1">🥉</div>
                                    <div class="font-bold text-gray-900" x-text="bracketData.results.juara3.contingent?.name ?? '-'"></div>
                                    <div class="text-xs text-gray-500">Juara 3</div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('bracketManager', (initialSports) => ({
        sports: initialSports,
        selectedSportId: sessionStorage.getItem('public_bagan_sport_id') || '',
        selectedCategoryId: sessionStorage.getItem('public_bagan_category_id') || '',
        registrations: [],
        bracketData: null,
        isLoading: false,

        init() {
            this.$watch('selectedSportId', val => {
                sessionStorage.setItem('public_bagan_sport_id', val);
            });
            this.$watch('selectedCategoryId', val => {
                sessionStorage.setItem('public_bagan_category_id', val || '');
            });
            
            if (this.selectedSportId) {
                this.loadBracket();
            }
        },

        get selectedSport() {
            return this.sports.find(s => s.id == this.selectedSportId) || null;
        },
        get selectedSportCategories() {
            return this.selectedSport?.categories || [];
        },
        get hasCategories() {
            return this.selectedSportCategories.length > 0;
        },
        get categoryOptionsHtml() {
            if (!this.hasCategories) {
                return '<option value="">Tidak ada sub-kategori</option>';
            }
            let html = '<option value="" disabled selected>Pilih sub-kategori...</option>';
            this.selectedSportCategories.forEach(c => {
                html += `<option value="${c.id}">${c.name}</option>`;
            });
            return html;
        },
        get isReady() {
            return this.selectedSportId && (!this.hasCategories || this.selectedCategoryId);
        },

        async api(method, url) {
            const opts = {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            };
            const res = await fetch('/api' + url, opts);
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Request failed');
            return json;
        },

        async loadBracket() {
            if (!this.selectedSportId) return;
            this.isLoading = true;
            try {
                let url = `/bracket?sport_id=${this.selectedSportId}`;
                if (this.selectedCategoryId) {
                    url += `&sport_category_id=${this.selectedCategoryId}`;
                }
                const res = await this.api('GET', url);
                this.bracketData = res.data;
            } catch (err) {
                console.error(err);
                this.bracketData = null;
            } finally {
                this.isLoading = false;
            }
        },

        onSportChange() {
            this.selectedCategoryId = '';
            this.bracketData = null;
        },

        onCategoryChange() {
            this.bracketData = null;
        },

        handleSearch() {
            if (this.isReady) {
                this.loadBracket();
            }
        }
    }));
    });
</script>
</x-layout-public>
    