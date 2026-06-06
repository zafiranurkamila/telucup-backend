<x-layouts.dashboard :roleLabel="'PIC Kontingen'">
    <x-slot:title>Jadwal & Pertandingan</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-pic')
    </x-slot:sidebar>

    <div class="space-y-8 pb-10 max-w-full overflow-x-hidden">
        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                Jadwal & Pertandingan
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                Pantau jadwal pertandingan, skor, dan bagan turnamen dari cabang olahraga yang diikuti.
            </p>
        </div>

        {{-- Daftar Semua Pertandingan Kontingen --}}
        <div class="space-y-4">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 border-b border-gray-200 pb-2">
                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path d="M3 6h18M6 12h12m-9 6h6"></path>
                </svg>
                Semua Pertandingan Kontingen
            </h2>
            
            @if(count($allMatches) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($allMatches as $match)
                        <x-match.card :match="$match" />
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-gray-500">
                    Tidak ada jadwal pertandingan untuk kontingen ini.
                </div>
            @endif
        </div>

        <div class="h-px bg-gray-200 w-full my-4"></div>

        {{-- Bagan Turnamen (Alpine JS) --}}
        <div class="space-y-4" x-data="bracketManager(@js($sports), 'pic_kontingen')">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2 border-b border-gray-200 pb-2">
                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                </svg>
                Bagan Turnamen
            </h2>

            {{-- Filter Panel (Read Only for PIC) --}}
            <x-bracket.admin-panel :sports="$sports" role="pic_kontingen" />

            {{-- Empty state --}}
            <template x-if="!bracketData && !selectedSportId && !isLoading">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-gray-50 flex items-center justify-center mb-4 border border-gray-100">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">
                        Belum Ada Data Bagan
                    </h3>
                    <p class="text-sm text-gray-400 max-w-sm mx-auto">
                        Silakan pilih cabang olahraga dan klik cari untuk melihat jadwal pertandingan dan bagan turnamen.
                    </p>
                </div>
            </template>

            {{-- Loading --}}
            <template x-if="isLoading">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center flex flex-col items-center">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand mb-4"></div>
                    <p class="text-gray-500">Memuat bagan pertandingan...</p>
                </div>
            </template>

            {{-- Instruction if bracket not generated yet --}}
            <template x-if="selectedSportId && !bracketData && !isLoading && isReady">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-5">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-700 mb-2">Bagan Belum Digenerate</h3>
                    <p class="text-sm text-gray-500 max-w-md mx-auto">Panitia belum melakukan generate bagan untuk cabang olahraga ini.</p>
                </div>
            </template>

            {{-- Bracket rounds --}}
            <template x-if="bracketData && !isLoading">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 overflow-hidden">
                    <x-bracket.board role="pic_kontingen" />
                </div>
            </template>
            
        </div>
    </div>
</x-layouts.dashboard>
