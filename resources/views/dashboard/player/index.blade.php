<x-layouts.dashboard :roleLabel="'Player'">
    <x-slot:title>Dashboard Player</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-player')
    </x-slot:sidebar>

    <div class="space-y-6 pb-10">
        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, {{ Auth::user()->name }}!</h1>
            <p class="text-gray-500 text-sm mt-1">Berikut ringkasan akun kamu sebagai peserta Tel-U Cup.</p>
        </div>

        {{-- Profile Card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-5">
                {{-- Avatar --}}
                <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden shrink-0 border-2 border-gray-200">
                    @if($player?->photo_path)
                        <img src="{{ $player->photo_path }}" alt="Foto profil" class="w-full h-full object-cover">
                    @else
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>

                    @if($player)
                        <div class="flex flex-wrap gap-3 mt-2 text-sm text-gray-600">
                            @if($player->nim_nip)
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                    {{ $player->nim_nip }}
                                </span>
                            @endif
                            @if($player->contingent)
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    {{ $player->contingent->name }}
                                </span>
                            @endif
                            @if($player->risk_lvl)
                                <x-badge :type="$player->risk_lvl === 'high' ? 'high' : ($player->risk_lvl === 'medium' ? 'medium' : 'low')">
                                    Risk: {{ ucfirst($player->risk_lvl) }}
                                </x-badge>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('dashboard.player.profil.show') }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-brand/30 hover:shadow-sm transition-all group">
                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-semibold text-gray-800 group-hover:text-brand transition-colors">Profil Saya</h3>
                <p class="text-xs text-gray-500 mt-1">Lihat dan kelola profil kamu</p>
            </a>

            <a href="{{ route('dashboard.player.self-assessment.index') }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-brand/30 hover:shadow-sm transition-all group">
                <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <h3 class="font-semibold text-gray-800 group-hover:text-brand transition-colors">Self Assessment</h3>
                <p class="text-xs text-gray-500 mt-1">Isi assessment kesehatan kamu</p>
            </a>

            <a href="{{ route('dashboard.player.index') }}/galeri" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-brand/30 hover:shadow-sm transition-all group">
                <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="font-semibold text-gray-800 group-hover:text-brand transition-colors">Galeri Saya</h3>
                <p class="text-xs text-gray-500 mt-1">Foto kamu dari event Tel-U Cup</p>
            </a>
        </div>
    </div>
</x-layouts.dashboard>
