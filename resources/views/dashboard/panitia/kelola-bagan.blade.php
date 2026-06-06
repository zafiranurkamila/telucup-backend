<x-layouts.dashboard :roleLabel="'Super Admin'">
    <x-slot:title>Kelola Bagan</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-panitia')
    </x-slot:sidebar>

    {{-- Alpine.js state container --}}
    <div
        x-data="bracketManager(@js($sports), 'panitia')"
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

        {{-- Toast Notification --}}
        <div 
            x-show="showToastMsg" 
            x-transition
            class="fixed top-4 right-4 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border"
            :class="toastType === 'error' ? 'bg-red-50 border-red-200 text-red-800' : (toastType === 'info' ? 'bg-blue-50 border-blue-200 text-blue-800' : 'bg-green-50 border-green-200 text-green-800')"
            style="display: none;"
        >
            <span x-text="toastMessage" class="text-sm font-semibold"></span>
        </div>

        {{-- Filter & Admin Panel --}}
        <x-bracket.admin-panel :sports="$sports" role="panitia" />

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
            <x-bracket.board role="panitia" />
        </template>

        {{-- Match Edit Modal --}}
        <x-bracket.edit-modal />
    </div>

</x-layouts.dashboard>
