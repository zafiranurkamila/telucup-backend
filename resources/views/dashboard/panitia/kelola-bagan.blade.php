<x-layouts.dashboard :roleLabel="'Super Admin'">
    <x-slot:title>Kelola Bagan</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-panitia')
    </x-slot:sidebar>

    {{-- Alpine.js state container --}}
    <div
        x-data="bracketManager(@js($sports))"
        class="space-y-6 pb-10"
    >
        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                Kelola Bagan & Pertandingan
            </h1>
            <p class="text-gray-500 text-sm mt-1">Generate, kelola, dan pantau bagan pertandingan per cabang olahraga.</p>
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
                                <option value="{{ $s->id }}">{{ $s->icon ?? '' }} {{ $s->name }}</option>
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
                            @change="onCategoryChange()"
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

                {{-- Tim count --}}
                <div class="md:col-span-3 flex flex-col items-start md:items-center justify-end">
                    <div class="flex items-center gap-2 bg-gray-50 border border-gray-100 rounded-lg px-4 py-2.5 w-full justify-center">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <div class="text-center">
                            <span class="text-lg font-extrabold text-gray-900" x-text="registrations.length"></span>
                            <span class="text-[10px] font-bold text-gray-400 block -mt-1">TIM TERVERIFIKASI</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 my-4"></div>

            {{-- Action buttons --}}
            <div class="flex flex-wrap gap-3 items-center">
                {{-- Generate --}}
                <template x-if="!bracketData">
                    <button
                        @click="handleGenerate()"
                        :disabled="!isReady || isGenerating"
                        class="inline-flex items-center gap-2 bg-brand hover:bg-brand-hover disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-sm font-bold py-2.5 px-5 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 shadow-sm hover:shadow-md"
                    >
                        <svg :class="isGenerating ? 'animate-spin' : ''" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        <span x-text="isGenerating ? 'Generating...' : 'Generate Bagan'"></span>
                    </button>
                </template>

                {{-- Randomize + Reset --}}
                <template x-if="bracketData">
                    <div class="flex flex-wrap gap-3">
                        <button
                            @click="handleRandomize()"
                            class="inline-flex items-center gap-2 bg-brand hover:bg-brand-hover text-white text-sm font-bold py-2.5 px-5 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500/50 shadow-sm hover:shadow-md"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Randomize Posisi
                        </button>
                        <button
                            @click="handleReset()"
                            class="inline-flex items-center gap-2 bg-white hover:bg-red-50 border border-red-200 text-brand text-sm font-bold py-2.5 px-5 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-200"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Reset Bagan
                        </button>
                    </div>
                </template>

                {{-- Hints --}}
                <template x-if="!selectedSportId">
                    <span class="text-xs text-gray-400 font-medium italic ml-2">← Pilih cabang olahraga terlebih dahulu</span>
                </template>
                <template x-if="selectedSportId && hasCategories && !selectedCategoryId">
                    <span class="text-xs text-amber-500 font-medium italic ml-2">⚠ Pilih sub-kategori untuk melanjutkan</span>
                </template>
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

        {{-- Instructions (sport selected, no bracket yet) --}}
        <template x-if="selectedSportId && !bracketData && !isLoading && isReady">
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
                <h3 class="font-bold text-blue-900 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Bagan belum digenerate
                </h3>
                <p class="text-sm text-blue-800 mb-1">
                    Terdapat <strong x-text="registrations.length"></strong> tim terverifikasi.
                    Klik <strong>"Generate Bagan"</strong> untuk membuat bagan turnamen otomatis.
                </p>
                <p class="text-xs text-blue-600">Bagan akan dibuat dalam format single-elimination dengan bye otomatis jika jumlah tim tidak genap.</p>
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
                <div class="overflow-x-auto -mx-6 px-6 pb-4">
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
                                                    @click="openMatchEdit(match)"
                                                    :class="{
                                                        'ring-2 ring-brand': editingMatchId === match.id,
                                                        'opacity-50': match.status === 'bye',
                                                        'cursor-pointer hover:shadow-md hover:-translate-y-0.5': match.status !== 'bye',
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
                                                        :draggable="['scheduled', 'bye'].includes(match.status)"
                                                        @dragstart="dragStartMatch($event, match, 'a')"
                                                        @dragover.prevent="if(['scheduled', 'bye'].includes(match.status)) { $event.dataTransfer.dropEffect = 'move'; $event.target.closest('.px-3').classList.add('ring-2', 'ring-brand', 'ring-inset'); }"
                                                        @dragleave="$event.target.closest('.px-3')?.classList.remove('ring-2', 'ring-brand', 'ring-inset')"
                                                        @dragend="$event.target.classList.remove('opacity-50')"
                                                        @drop="dropMatch($event, match, 'a')"
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
                                                        :draggable="['scheduled', 'bye'].includes(match.status)"
                                                        @dragstart="dragStartMatch($event, match, 'b')"
                                                        @dragover.prevent="if(['scheduled', 'bye'].includes(match.status)) { $event.dataTransfer.dropEffect = 'move'; $event.target.closest('.px-3').classList.add('ring-2', 'ring-brand', 'ring-inset'); }"
                                                        @dragleave="$event.target.closest('.px-3')?.classList.remove('ring-2', 'ring-brand', 'ring-inset')"
                                                        @dragend="$event.target.classList.remove('opacity-50')"
                                                        @drop="dropMatch($event, match, 'b')"
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
                                                @click="openMatchEdit(bracketData.third_place_match)"
                                                class="bg-white rounded-xl border border-gray-200 shadow-sm cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition-all duration-150 overflow-hidden"
                                            >
                                                <div class="flex items-center justify-between px-3 py-1.5 border-b border-gray-100 bg-gray-50/50">
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase flex items-center gap-1">
                                                        <svg class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                        <span x-text="'MATCH #' + bracketData.third_place_match.match_number"></span>
                                                    </span>
                                                    <span class="text-[10px] font-bold text-gray-500 uppercase" x-text="bracketData.third_place_match.status === 'finished' ? 'SELESAI' : bracketData.third_place_match.status"></span>
                                                </div>
                                                <div class="px-3 py-2.5 flex items-center justify-between"
                                                    :draggable="['scheduled', 'bye'].includes(bracketData.third_place_match.status)"
                                                    @dragstart="dragStartMatch($event, bracketData.third_place_match, 'a')"
                                                    @dragover.prevent="if(['scheduled', 'bye'].includes(bracketData.third_place_match.status)) { $event.dataTransfer.dropEffect = 'move'; $event.target.closest('.px-3').classList.add('ring-2', 'ring-brand', 'ring-inset'); }"
                                                    @dragleave="$event.target.closest('.px-3')?.classList.remove('ring-2', 'ring-brand', 'ring-inset')"
                                                    @dragend="$event.target.classList.remove('opacity-50')"
                                                    @drop="dropMatch($event, bracketData.third_place_match, 'a')"
                                                >
                                                    <div class="flex items-center gap-2 overflow-hidden pointer-events-none">
                                                        <img :src="bracketData.third_place_match.team_a?.contingent?.logo_url || 'https://ui-avatars.com/api/?name=' + (bracketData.third_place_match.team_a?.contingent?.name || 'A') + '&background=f3f4f6&color=9ca3af'" class="w-6 h-6 rounded-full object-cover shrink-0">
                                                        <span class="text-sm font-bold text-gray-800 truncate" x-text="bracketData.third_place_match.team_a?.contingent?.name ?? 'TBD'"></span>
                                                    </div>
                                                    <span class="text-sm font-black ml-2 tabular-nums" x-text="bracketData.third_place_match.score_a ?? ''"></span>
                                                </div>
                                                <div class="border-t border-gray-100 mx-3"></div>
                                                <div class="px-3 py-2.5 flex items-center justify-between"
                                                    :draggable="['scheduled', 'bye'].includes(bracketData.third_place_match.status)"
                                                    @dragstart="dragStartMatch($event, bracketData.third_place_match, 'b')"
                                                    @dragover.prevent="if(['scheduled', 'bye'].includes(bracketData.third_place_match.status)) { $event.dataTransfer.dropEffect = 'move'; $event.target.closest('.px-3').classList.add('ring-2', 'ring-brand', 'ring-inset'); }"
                                                    @dragleave="$event.target.closest('.px-3')?.classList.remove('ring-2', 'ring-brand', 'ring-inset')"
                                                    @dragend="$event.target.classList.remove('opacity-50')"
                                                    @drop="dropMatch($event, bracketData.third_place_match, 'b')"
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

        {{-- ============================================================
             Match Edit Modal
             ============================================================ --}}
        <div
            x-show="editingMatch"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center px-4"
            style="display: none;"
        >
            <div class="fixed inset-0 bg-black/50" @click="editingMatch = null"></div>

            <div
                x-show="editingMatch"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto z-10"
            >
                <template x-if="editingMatch">
                    <div>
                        {{-- Modal header --}}
                        <div class="flex items-center justify-between p-5 border-b border-gray-100">
                            <h3 class="text-lg font-bold text-gray-900">
                                Edit Pertandingan <span class="text-brand" x-text="'#' + editingMatch.match_number"></span>
                            </h3>
                            <button @click="editingMatch = null" class="p-1 rounded-lg hover:bg-gray-100 transition-colors">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Modal body --}}
                        <div class="p-5 space-y-5">
                            {{-- Teams --}}
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-3 gap-3">
                                    <div class="flex-1">
                                        <label class="block text-[10px] text-center text-gray-400 uppercase mb-1">Tim A</label>
                                        <div x-show="editingMatch && ['scheduled', 'bye'].includes(editingMatch.status)">
                                            <select x-model="editForm.registration_a_id" class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none">
                                                <option value="">-- TBD --</option>
                                                <template x-for="reg in registrations" :key="reg.id">
                                                    <option :value="String(reg.id)" x-text="reg.contingent ? reg.contingent.name : 'Unknown'"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div x-show="editingMatch && !['scheduled', 'bye'].includes(editingMatch.status)">
                                            <div class="text-sm font-bold text-gray-800 text-center" x-text="editingMatch?.team_a?.contingent?.name ?? 'TBD'"></div>
                                        </div>
                                    </div>
                                    <div class="text-xl font-black text-gray-900 shrink-0">VS</div>
                                    <div class="flex-1">
                                        <label class="block text-[10px] text-center text-gray-400 uppercase mb-1">Tim B</label>
                                        <div x-show="editingMatch && ['scheduled', 'bye'].includes(editingMatch.status)">
                                            <select x-model="editForm.registration_b_id" class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none">
                                                <option value="">-- TBD --</option>
                                                <template x-for="reg in registrations" :key="reg.id">
                                                    <option :value="String(reg.id)" x-text="reg.contingent ? reg.contingent.name : 'Unknown'"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div x-show="editingMatch && !['scheduled', 'bye'].includes(editingMatch.status)">
                                            <div class="text-sm font-bold text-gray-800 text-center" x-text="editingMatch?.team_b?.contingent?.name ?? 'TBD'"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Schedule fields & Status --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Status Pertandingan</label>
                                    <select x-model="editForm.status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none" :disabled="['bye'].includes(editingMatch.status)">
                                        <option value="scheduled">Terjadwal</option>
                                        <option value="live">Sedang Bermain (Live)</option>
                                        <option value="finished">Selesai</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal</label>
                                    <input type="date" x-model="editForm.match_date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Waktu</label>
                                    <input type="time" x-model="editForm.match_time" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Lokasi</label>
                                    <input type="text" x-model="editForm.location" placeholder="Gedung Sport Center Lt. 2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Wasit</label>
                                <input type="text" x-model="editForm.referee_name" placeholder="Nama wasit" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none">
                            </div>

                            {{-- Score (only for live/finish) --}}
                            <template x-if="editingMatch.status === 'live' || editForm.finish_mode">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-2">Skor Akhir</label>
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 text-center">
                                            <div class="text-xs text-gray-400 mb-1" x-text="editingMatch.team_a?.contingent?.name ?? 'Tim A'"></div>
                                            <input type="number" x-model.number="editForm.score_a" min="0" class="w-full text-center border border-gray-200 rounded-lg px-3 py-2 text-lg font-bold focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none">
                                        </div>
                                        <span class="text-gray-300 font-bold text-xl">—</span>
                                        <div class="flex-1 text-center">
                                            <div class="text-xs text-gray-400 mb-1" x-text="editingMatch.team_b?.contingent?.name ?? 'Tim B'"></div>
                                            <input type="number" x-model.number="editForm.score_b" min="0" class="w-full text-center border border-gray-200 rounded-lg px-3 py-2 text-lg font-bold focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none">
                                        </div>
                                    </div>

                                    {{-- Winner selection for tie --}}
                                    <template x-if="editForm.score_a === editForm.score_b && editForm.score_a >= 0 && editingMatch.team_a && editingMatch.team_b">
                                        <div class="mt-3">
                                            <label class="block text-xs font-semibold text-amber-600 mb-1">⚠ Skor seri — pilih pemenang:</label>
                                            <select x-model="editForm.winner_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none">
                                                <option value="">-- Pilih Pemenang --</option>
                                                <template x-if="editingMatch.team_a">
                                                    <option :value="String(editingMatch.team_a?.registration_id)" x-text="editingMatch.team_a?.contingent?.name"></option>
                                                </template>
                                                <template x-if="editingMatch.team_b">
                                                    <option :value="String(editingMatch.team_b?.registration_id)" x-text="editingMatch.team_b?.contingent?.name"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Catatan</label>
                                <textarea x-model="editForm.notes" rows="2" placeholder="Catatan tambahan..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:outline-none resize-none"></textarea>
                            </div>
                        </div>

                        {{-- Modal footer --}}
                        <div class="flex items-center justify-between p-5 border-t border-gray-100 bg-gray-50/50 gap-3">
                            <div class="flex gap-2">
                                {{-- Start match / Verifikasi --}}
                                <template x-if="editingMatch.status === 'scheduled' && editingMatch.team_a && editingMatch.team_b">
                                    <div class="flex gap-2">
                                        <a
                                            :href="`{{ route('dashboard.panitia.verifikasi') }}?match_id=${editingMatch.id}`"
                                            class="inline-flex items-center gap-1.5 text-sm font-bold text-brand bg-brand/10 hover:bg-brand/20 px-4 py-2 rounded-lg transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Verifikasi Tim
                                        </a>
                                        <button
                                            @click="handleStartMatch(editingMatch.id)"
                                            class="inline-flex items-center gap-1.5 text-sm font-bold text-green-700 bg-green-100 hover:bg-green-200 px-4 py-2 rounded-lg transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Mulai
                                        </button>
                                    </div>
                                </template>

                                {{-- Finish match --}}
                                <template x-if="editingMatch.status === 'live'">
                                    <button
                                        @click="editForm.finish_mode = true"
                                        class="inline-flex items-center gap-1.5 text-sm font-bold text-blue-700 bg-blue-100 hover:bg-blue-200 px-4 py-2 rounded-lg transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Selesaikan
                                    </button>
                                </template>
                            </div>

                            <div class="flex gap-2">
                                <button @click="editingMatch = null" class="text-sm font-medium text-gray-600 hover:text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">Batal</button>
                                <button
                                    @click="saveMatch()"
                                    :disabled="isSaving"
                                    class="inline-flex items-center gap-1.5 text-sm font-bold text-white bg-brand hover:bg-brand-hover px-5 py-2 rounded-lg transition-colors disabled:opacity-50"
                                >
                                    <span x-text="isSaving ? (editForm.finish_mode ? 'Menyelesaikan...' : 'Menyimpan...') : (editForm.finish_mode ? 'Akhiri Pertandingan' : 'Simpan')"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ============================================================
             Toast
             ============================================================ --}}
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

{{-- ============================================================
     Alpine.js — bracketManager component
     ============================================================ --}}
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bracketManager', (initialSports) => ({
        // State
        sports: initialSports,
        selectedSportId: sessionStorage.getItem('bagan_sport_id') || '',
        selectedCategoryId: sessionStorage.getItem('bagan_category_id') || '',
        registrations: [],
        bracketData: null,
        isGenerating: false,
        isLoading: false,
        isSaving: false,
        editingMatch: null,
        editingMatchId: null,
        editForm: {},
        toast: { show: false, message: '', type: 'info' },
        refreshInterval: null,

        init() {
            this.$watch('selectedSportId', val => {
                sessionStorage.setItem('bagan_sport_id', val);
            });
            this.$watch('selectedCategoryId', val => {
                sessionStorage.setItem('bagan_category_id', val || '');
            });
            
            if (this.selectedSportId) {
                this.fetchRegistrations().then(() => this.loadBracket());
            }
        },

        // Computed
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
            return this.selectedSportId &&
                (!this.hasCategories || this.selectedCategoryId) &&
                this.registrations.length >= 2;
        },

        // API helper
        async api(method, url, data = null, prefix = '/api') {
            const opts = {
                method,
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            };
            if (data && method !== 'GET') opts.body = JSON.stringify(data);
            if (data && method === 'GET') {
                const params = new URLSearchParams(Object.entries(data).filter(([,v]) => v != null));
                url += '?' + params.toString();
            }
            const res = await fetch(prefix + url, opts);
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Request failed');
            return json;
        },

        // Show toast
        showToast(message, type = 'info') {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, 3500);
        },

        // Fetch registrations
        async fetchRegistrations() {
            try {
                let url = '/registrations';
                const params = ['per_page=1000'];
                if (this.selectedSportId) params.push(`sport_id=${this.selectedSportId}`);
                if (this.selectedCategoryId) params.push(`sport_category_id=${this.selectedCategoryId}`);
                url += `?${params.join('&')}`;

                const res = await this.api('GET', url);
                // The API returns { status: 'success', data: { data: [...], current_page: ... } }
                let items = [];
                if (Array.isArray(res)) {
                    items = res;
                } else if (res && Array.isArray(res.data)) {
                    items = res.data;
                } else if (res && res.data && Array.isArray(res.data.data)) {
                    items = res.data.data;
                }
                
                this.registrations = items.filter(r => r?.status === 'verified');
            } catch (e) {
                console.error('Failed to load registrations', e);
                this.registrations = [];
            }
        },

        // Load bracket
        async loadBracket() {
            if (!this.selectedSportId) return;
            if (this.hasCategories && !this.selectedCategoryId) return;
            try {
                const params = { sport_id: this.selectedSportId };
                if (this.selectedCategoryId) params.sport_category_id = this.selectedCategoryId;
                const res = await this.api('GET', '/bracket', params);
                this.bracketData = res.data;
            } catch (e) {
                if (!e.message?.includes('404')) {
                    // Ignore 404 (no bracket yet)
                }
                this.bracketData = null;
            }
        },

        // Start auto-refresh
        startRefresh() {
            this.stopRefresh();
            this.refreshInterval = setInterval(() => this.loadBracket(), 5000);
        },
        stopRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
                this.refreshInterval = null;
            }
        },

        // Event handlers
        dragStartMatch(event, match, slot) {
            // Only allow dragging if match is scheduled or bye, and there is a team
            if (!['scheduled', 'bye'].includes(match.status)) {
                event.preventDefault();
                return;
            }
            
            const team = slot === 'a' ? match.team_a : match.team_b;
            if (!team) {
                event.preventDefault();
                return;
            }

            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', JSON.stringify({
                sourceMatchId: match.id,
                sourceSlot: slot,
                registrationId: team.registration_id
            }));
            
            // Highlight styling during drag
            event.target.classList.add('opacity-50');
        },

        async dropMatch(event, targetMatch, targetSlot) {
            event.target.classList.remove('opacity-50', 'ring-2', 'ring-brand', 'ring-inset');
            
            if (!['scheduled', 'bye'].includes(targetMatch.status)) {
                this.showToast('Hanya bisa memindahkan tim ke pertandingan yang masih terjadwal', 'error');
                return;
            }

            try {
                const data = JSON.parse(event.dataTransfer.getData('text/plain'));
                if (!data || !data.registrationId) return;

                // If dropping to the exact same slot, do nothing
                if (data.sourceMatchId === targetMatch.id && data.sourceSlot === targetSlot) return;

                this.isSaving = true;
                
                // Call API to set teams on target match.
                // The backend auto-swap logic will automatically move targetMatch's current team to sourceMatch.
                const payload = {};
                if (targetSlot === 'a') {
                    payload.registration_a_id = data.registrationId;
                } else {
                    payload.registration_b_id = data.registrationId;
                }

                await this.api('PATCH', `/matches/${targetMatch.id}/teams`, payload, '/dashboard/panitia');
                
                await this.loadBracket();
                this.showToast('Tim berhasil ditukar!', 'success');
            } catch (e) {
                this.showToast(e.message || 'Gagal menukar tim', 'error');
            } finally {
                this.isSaving = false;
            }
        },

        async onSportChange() {
            this.selectedCategoryId = '';
            this.bracketData = null;
            this.editingMatch = null;
            this.registrations = [];
            this.stopRefresh();

            if (!this.hasCategories) {
                this.isLoading = true;
                await this.fetchRegistrations();
                await this.loadBracket();
                this.isLoading = false;
                this.startRefresh();
            }
        },

        async onCategoryChange() {
            this.bracketData = null;
            this.editingMatch = null;
            this.stopRefresh();

            if (this.selectedCategoryId) {
                this.isLoading = true;
                await this.fetchRegistrations();
                await this.loadBracket();
                this.isLoading = false;
                this.startRefresh();
            }
        },

        async handleGenerate() {
            if (!this.isReady) return;
            this.isGenerating = true;
            try {
                const payload = { sport_id: parseInt(this.selectedSportId) };
                if (this.selectedCategoryId) payload.sport_category_id = parseInt(this.selectedCategoryId);
                await this.api('POST', '/bracket/generate', payload, '/dashboard/panitia');
                await this.loadBracket();
                this.showToast(`Bagan berhasil digenerate untuk ${this.registrations.length} tim!`, 'success');
                this.startRefresh();
            } catch (e) {
                this.showToast(e.message || 'Gagal generate bagan', 'error');
            } finally {
                this.isGenerating = false;
            }
        },

        async handleRandomize() {
            if (!confirm('Ini akan menghapus seluruh jadwal dan skor saat ini. Anda yakin ingin mengacak ulang?')) return;
            this.isGenerating = true;
            try {
                await this.api('DELETE', '/bracket/reset', { sport_id: parseInt(this.selectedSportId), sport_category_id: this.selectedCategoryId ? parseInt(this.selectedCategoryId) : null }, '/dashboard/panitia');
                const payload = { sport_id: parseInt(this.selectedSportId) };
                if (this.selectedCategoryId) payload.sport_category_id = parseInt(this.selectedCategoryId);
                await this.api('POST', '/bracket/generate', payload, '/dashboard/panitia');
                await this.loadBracket();
                this.showToast('Posisi tim berhasil diacak ulang!', 'info');
            } catch (e) {
                this.showToast(e.message || 'Gagal mengacak ulang bagan', 'error');
            } finally {
                this.isGenerating = false;
            }
        },

        async handleReset() {
            if (!confirm('Bagan akan dihapus permanen. Anda yakin?')) return;
            try {
                await this.api('DELETE', '/bracket/reset', { sport_id: parseInt(this.selectedSportId), sport_category_id: this.selectedCategoryId ? parseInt(this.selectedCategoryId) : null }, '/dashboard/panitia');
                this.bracketData = null;
                this.editingMatch = null;
                this.stopRefresh();
                this.showToast('Bagan berhasil dihapus.', 'info');
            } catch (e) {
                this.showToast(e.message || 'Gagal reset bagan', 'error');
            }
        },

        openMatchEdit(match) {
            this.editingMatch = match;
            this.editingMatchId = match.id;
            this.$nextTick(() => { this.editForm = {
                match_date: match.match_date || '',
                match_time: match.match_time || '',
                location: match.location || '',
                referee_name: match.referee_name || '',
                notes: match.notes || '',
                score_a: match.score_a || 0,
                score_b: match.score_b || 0,
                winner_id: match.winner?.registration_id ? String(match.winner.registration_id) : '',
                registration_a_id: match.team_a?.registration_id ? String(match.team_a.registration_id) : '',
                registration_b_id: match.team_b?.registration_id ? String(match.team_b.registration_id) : '',
                status: match.status,
                finish_mode: false,
            }; });
        },

        async handleStartMatch(matchId) {
            try {
                await this.api('PATCH', `/matches/${matchId}/status`, { status: 'live' }, '/dashboard/panitia');
                await this.loadBracket();
                this.editingMatch = null;
                this.editingMatchId = null;
                this.showToast('Pertandingan dimulai!', 'success');
            } catch (e) {
                this.showToast(e.message || 'Gagal memulai pertandingan', 'error');
            }
        },

        async saveMatch() {
            if (!this.editingMatch) return;
            this.isSaving = true;
            const matchId = this.editingMatch.id;

            try {
                // 1. Update teams if scheduled or bye
                if (['scheduled', 'bye'].includes(this.editingMatch.status)) {
                    const oldRegA = this.editingMatch.team_a?.registration_id ? String(this.editingMatch.team_a.registration_id) : '';
                    const oldRegB = this.editingMatch.team_b?.registration_id ? String(this.editingMatch.team_b.registration_id) : '';
                    
                    if (this.editForm.registration_a_id !== oldRegA || this.editForm.registration_b_id !== oldRegB) {
                        await this.api('PATCH', `/matches/${matchId}/teams`, {
                            registration_a_id: this.editForm.registration_a_id || null,
                            registration_b_id: this.editForm.registration_b_id || null,
                        }, '/dashboard/panitia');
                    }
                }

                // 2. Update schedule
                await this.api('PATCH', `/matches/${matchId}/schedule`, {
                    match_date: this.editForm.match_date || null,
                    match_time: this.editForm.match_time || null,
                    location: this.editForm.location || null,
                    referee_name: this.editForm.referee_name || null,
                    notes: this.editForm.notes || null,
                }, '/dashboard/panitia');

                // 3. Update score if finished
                if (this.editForm.finish_mode) {
                    const payload = {
                        score_a: parseInt(this.editForm.score_a) || 0,
                        score_b: parseInt(this.editForm.score_b) || 0,
                    };
                    if (this.editForm.score_a === this.editForm.score_b) {
                        payload.winner_registration_id = this.editForm.winner_id;
                    }
                    await this.api('PATCH', `/matches/${matchId}/score`, payload, '/dashboard/panitia');
                }

                // 4. Update status if explicitly changed
                if (!this.editForm.finish_mode && this.editForm.status && this.editForm.status !== this.editingMatch.status) {
                    await this.api('PATCH', `/matches/${matchId}/status`, { status: this.editForm.status }, '/dashboard/panitia');
                }

                await this.loadBracket();
                this.showToast(this.editForm.finish_mode ? 'Pertandingan diselesaikan!' : 'Pertandingan berhasil disimpan!', 'success');
                
                if (this.editForm.finish_mode) {
                    this.editingMatch = null;
                }
            } catch (e) {
                this.showToast(e.message || 'Gagal menyimpan pertandingan', 'error');
            } finally {
                this.isSaving = false;
            }
        },

        // Cleanup
        destroy() {
            this.stopRefresh();
        }
    }));
});
</script>
@endpush
</x-layouts.dashboard>
