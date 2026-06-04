<x-layouts.dashboard :roleLabel="'PIC Kontingen'">
    <x-slot:title>Dashboard PIC Kontingen</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-pic')
    </x-slot:sidebar>

    <div class="space-y-6 pb-10">
        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard PIC Kontingen</h1>
            <p class="text-gray-500 text-sm mt-1">
                Selamat datang, {{ $user->name }}.
                @if($contingent)
                    Anda mengelola kontingen <strong>{{ $contingent->name }}</strong>.
                @else
                    <span class="text-orange-600">Anda belum ditetapkan sebagai PIC kontingen manapun.</span>
                @endif
            </p>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stats-card
                label="Anggota Kontingen"
                :value="$playerCount"
                subtext="Pemain terdaftar"
                iconBg="bg-blue-50"
                iconColor="text-blue-600"
            >
                <x-slot:icon>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </x-slot:icon>
            </x-stats-card>

            <x-stats-card
                label="Tim Terdaftar"
                :value="$registrationCount"
                subtext="Cabang olahraga"
                iconBg="bg-emerald-50"
                iconColor="text-emerald-600"
            >
                <x-slot:icon>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </x-slot:icon>
            </x-stats-card>

            <x-stats-card
                label="Pertandingan Hari Ini"
                :value="$todayMatches->count()"
                subtext="Jadwal kontingen"
                iconBg="bg-orange-50"
                iconColor="text-orange-600"
            >
                <x-slot:icon>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </x-slot:icon>
            </x-stats-card>
        </div>

        {{-- Pertandingan Hari Ini --}}
        <div>
            <h2 class="text-lg font-bold text-gray-800 mb-4">Pertandingan Hari Ini</h2>

            @if($todayMatches->isEmpty())
                <div class="bg-white p-6 rounded-xl border border-gray-200 text-center text-gray-500">
                    Tidak ada pertandingan kontingen kamu hari ini.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($todayMatches as $match)
                        <div class="bg-white rounded-xl border border-gray-200 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-semibold text-gray-500 uppercase">
                                    {{ $match->sport->name ?? '-' }}
                                    @if($match->round_name) &bull; {{ $match->round_name }} @endif
                                </span>
                                <x-badge :type="$match->status === 'completed' ? 'success' : ($match->status === 'ongoing' ? 'warning' : 'default')">
                                    {{ ucfirst($match->status ?? 'scheduled') }}
                                </x-badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-gray-800">
                                    {{ $match->registrationA?->contingent?->name ?? 'TBD' }}
                                </div>
                                <div class="text-lg font-bold text-gray-900 px-3">
                                    {{ $match->score_a ?? 0 }} — {{ $match->score_b ?? 0 }}
                                </div>
                                <div class="text-sm font-semibold text-gray-800 text-right">
                                    {{ $match->registrationB?->contingent?->name ?? 'TBD' }}
                                </div>
                            </div>
                            @if($match->match_time || $match->location)
                                <div class="mt-2 text-xs text-gray-400">
                                    @if($match->match_time) {{ $match->match_time }} @endif
                                    @if($match->location) &bull; {{ $match->location }} @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Quick Links --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('dashboard.pic.index') }}/anggota" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-brand/30 hover:shadow-sm transition-all group">
                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="font-semibold text-gray-800 text-sm group-hover:text-brand transition-colors">Kelola Anggota</h3>
            </a>

            <a href="{{ route('dashboard.pic.index') }}/registrasi" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-brand/30 hover:shadow-sm transition-all group">
                <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="font-semibold text-gray-800 text-sm group-hover:text-brand transition-colors">Registrasi Tim</h3>
            </a>

            <a href="{{ route('dashboard.pic.index') }}/jadwal" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-brand/30 hover:shadow-sm transition-all group">
                <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="font-semibold text-gray-800 text-sm group-hover:text-brand transition-colors">Jadwal Tanding</h3>
            </a>

            <a href="{{ route('dashboard.pic.index') }}/dokumentasi" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-brand/30 hover:shadow-sm transition-all group">
                <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="font-semibold text-gray-800 text-sm group-hover:text-brand transition-colors">Dokumentasi</h3>
            </a>
        </div>
    </div>
</x-layouts.dashboard>
