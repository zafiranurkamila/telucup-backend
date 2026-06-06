<x-layouts.dashboard :roleLabel="'Panitia'">
    <x-slot:title>Tinjauan Medis</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-panitia')
    </x-slot:sidebar>

    <div x-data="medisManager" class="space-y-6">
        
        <!-- Header & Stats Summary -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#B41F2A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    Tinjauan Medis Peserta
                </h1>
                <p class="text-gray-500 text-sm mt-1">Verifikasi hasil self-assessment medis peserta sebelum pertandingan.</p>
            </div>
            
            <div class="flex gap-3">
                <button @click="fetchAssessments(); fetchSummaries();" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Stats summary cards (High, Medium, Low, Clearance) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-5 border border-gray-100 flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Total Assessment</p>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1" x-text="totalItems">0</h3>
                    <p class="text-xs text-gray-400">Seluruh Peserta</p>
                </div>
                <div class="w-12 h-12 rounded-full flex items-center justify-center bg-gray-50 text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 border border-red-200 ring-1 ring-red-100 shadow-[0_0_15px_rgba(239,68,68,0.08)] flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">High Risk</p>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1" x-text="assessments.filter(a => a.risk_label === 'high').length">0</h3>
                    <p class="text-xs text-gray-400">Dalam halaman ini</p>
                </div>
                <div class="w-12 h-12 rounded-full flex items-center justify-center bg-red-50 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 border border-amber-100 flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Medium Risk</p>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1" x-text="assessments.filter(a => a.risk_label === 'medium').length">0</h3>
                    <p class="text-xs text-gray-400">Dalam halaman ini</p>
                </div>
                <div class="w-12 h-12 rounded-full flex items-center justify-center bg-amber-50 text-amber-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 border border-green-100 flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Low Risk</p>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1" x-text="assessments.filter(a => a.risk_label === 'low').length">0</h3>
                    <p class="text-xs text-gray-400">Dalam halaman ini</p>
                </div>
                <div class="w-12 h-12 rounded-full flex items-center justify-center bg-green-50 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200">
            <button @click="activeTab = 'list'" :class="activeTab === 'list' ? 'border-[#B41F2A] text-[#B41F2A]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-5 py-3.5 text-sm font-bold border-b-2 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Daftar Assessment
            </button>
            <button @click="activeTab = 'contingent'" :class="activeTab === 'contingent' ? 'border-[#B41F2A] text-[#B41F2A]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-5 py-3.5 text-sm font-bold border-b-2 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                Ringkasan Kontingen
            </button>
            <button @click="activeTab = 'sport'" :class="activeTab === 'sport' ? 'border-[#B41F2A] text-[#B41F2A]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-5 py-3.5 text-sm font-bold border-b-2 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Ringkasan Cabang Olahraga
            </button>
        </div>

        <!-- Content Area -->
        <div class="relative min-h-[400px]">
            <!-- Tab: List -->
            <div x-show="activeTab === 'list'" x-transition.opacity.duration.300ms>
                @include('dashboard.panitia.medis.partials.tab-list')
            </div>
            
            <!-- Tab: Contingent Summary -->
            <div x-show="activeTab === 'contingent'" x-transition.opacity.duration.300ms style="display: none;">
                @include('dashboard.panitia.medis.partials.tab-summary', ['type' => 'contingent'])
            </div>
            
            <!-- Tab: Sport Summary -->
            <div x-show="activeTab === 'sport'" x-transition.opacity.duration.300ms style="display: none;">
                @include('dashboard.panitia.medis.partials.tab-summary', ['type' => 'sport'])
            </div>
        </div>
        
        <!-- Modal Detail & Review -->
        <template x-if="selectedAssessment">
            @include('dashboard.panitia.medis.partials.modal-detail')
        </template>
        
    </div>
</x-layouts.dashboard>
