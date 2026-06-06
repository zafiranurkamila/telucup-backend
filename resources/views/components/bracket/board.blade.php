@props(['role' => 'public'])

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

                                    {{-- Use Match Card component --}}
                                    <x-bracket.match-card role="{{ $role }}" />

                                    <!-- Juara 3 -->
                                    <template x-if="rIndex === bracketData.rounds.length - 1 && bracketData.third_place_match">
                                        <div class="absolute top-full left-0 w-full mt-12 z-20">
                                            <div class="text-center mb-6">
                                                <span class="inline-block bg-[#a81d22] text-white text-[11px] font-bold px-8 py-2 rounded-full uppercase tracking-wider shadow-sm">JUARA 3</span>
                                            </div>
                                            <div class="relative w-full">
                                                <x-bracket.match-card match-var="bracketData.third_place_match" role="{{ $role }}" />
                                            </div>
                                        </div>
                                    </template>

                                </div>
                            </div>
                        </template>
                    </div>

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
