<x-layouts.dashboard :roleLabel="'Super Admin'">
    <x-slot:title>Dashboard Panitia</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-panitia')
    </x-slot:sidebar>

    <div class="space-y-6 pb-10">
        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard Overview</h1>
            <p class="text-gray-500 text-sm mt-1">Selamat datang di panel kontrol kepanitiaan Tel-U Cup.</p>
        </div>

        {{-- A. Quick Stats Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stats-card
                label="Total Kontingen"
                :value="$stats['totalKontingen']"
                subtext="Fakultas / Unit"
                iconBg="bg-blue-50"
                iconColor="text-blue-600"
            >
                <x-slot:icon>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </x-slot:icon>
            </x-stats-card>

            <x-stats-card
                label="Tim Menunggu Verifikasi"
                :value="$stats['timMenunggu']"
                subtext="Perlu ditinjau"
                iconBg="bg-orange-50"
                iconColor="text-orange-600"
                :highlight="$stats['timMenunggu'] > 0"
            >
                <x-slot:icon>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </x-slot:icon>
            </x-stats-card>

            <x-stats-card
                label="Pertandingan Hari Ini"
                :value="$stats['pertandinganHariIni']"
                subtext="Jadwal aktif"
                iconBg="bg-emerald-50"
                iconColor="text-emerald-600"
            >
                <x-slot:icon>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </x-slot:icon>
            </x-stats-card>

            <x-stats-card
                label="Peringatan Medis"
                :value="$stats['redFlags']"
                subtext="High Risk"
                iconBg="bg-red-50"
                iconColor="text-red-600"
                :highlight="$stats['redFlags'] > 0"
            >
                <x-slot:icon>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </x-slot:icon>
            </x-stats-card>
        </div>

        {{-- B. Widgets --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- Widget 1: Pertandingan Hari Ini --}}
            <div class="xl:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        Pertandingan Hari Ini
                    </h2>
                </div>

                @if($matchesToday->isEmpty())
                    <div class="bg-white p-6 rounded-xl border border-gray-200 text-center text-gray-500">
                        Tidak ada pertandingan yang dijadwalkan hari ini.
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($matchesToday as $match)
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

            {{-- Widget 2: Peringatan Medis + Kontingen --}}
            <div class="xl:col-span-1 space-y-6">
                {{-- Peringatan Medis --}}
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-red-50/30">
                        <h2 class="text-[15px] font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-[18px] h-[18px] text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            Peringatan Medis
                        </h2>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-center items-center text-center">
                        <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mb-3">
                            <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-gray-800 mb-1">{{ $stats['redFlags'] }} Pemain</h3>
                        <p class="text-sm text-gray-500 max-w-[200px] mb-4">
                            Terdeteksi memiliki riwayat medis berisiko tinggi (High Risk).
                        </p>
                        <a href="{{ route('dashboard.panitia.index') }}/medis" class="text-sm font-bold text-brand hover:underline flex items-center gap-1">
                            Tinjau Data Medis
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7m10 0v10"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Kontingen Terdaftar --}}
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-[15px] font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-[18px] h-[18px] text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Kontingen Terdaftar
                        </h2>
                    </div>

                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 text-gray-700 text-xs uppercase font-semibold border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3">Nama Kontingen</th>
                                    <th class="px-4 py-3 text-right">Pemain</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($contingents as $item)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-gray-800 text-[13px]">{{ $item->name }}</div>
                                            <div class="text-xs text-gray-500 mt-0.5">PIC: {{ $item->pic?->name ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="inline-flex items-center justify-center text-[11px] font-bold px-2 py-1 bg-gray-100 text-gray-600 rounded">
                                                {{ $item->players_count }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-8 text-center text-gray-400">Belum ada kontingen terdaftar</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 border-t border-gray-100 bg-gray-50 text-center shrink-0">
                        <a href="{{ route('dashboard.panitia.index') }}/kontingen" class="text-sm text-gray-600 hover:text-gray-900 font-medium transition-colors">
                            Lihat Seluruh Kontingen &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
