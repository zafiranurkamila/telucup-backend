<x-layouts.dashboard :roleLabel="'PIC Kontingen'">
    <x-slot:title>Dashboard PIC Kontingen</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-pic')
    </x-slot:sidebar>

    <div class="space-y-6 pb-10">
        @include('dashboard.pic-kontingen.partials.header')

        {{-- A. Quick Stats Cards --}}
        @include('dashboard.pic-kontingen.partials.quick-stats')

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- B. Widget 1: Jadwal Pertandingan Hari Ini --}}
            <div class="xl:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Pertandingan Hari Ini
                    </h2>
                    <a href="{{ url('/dashboard/pic-kontingen/jadwal') }}" class="text-sm text-brand font-medium hover:underline flex items-center gap-1">
                        Semua Jadwal 
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
                
                @include('dashboard.pic-kontingen.partials.today-matches')
            </div>

            {{-- C. Widget 2: Status Registrasi Tim & Ringkasan Kontingen --}}
            <div class="xl:col-span-1 space-y-6">
                @include('dashboard.pic-kontingen.partials.registration-status')
            </div>
        </div>
    </div>
</x-layouts.dashboard>
