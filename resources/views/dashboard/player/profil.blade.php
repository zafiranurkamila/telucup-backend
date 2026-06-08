<x-layouts.dashboard :roleLabel="'Player'">
    <x-slot:title>Profil Saya</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-player')
    </x-slot:sidebar>

    @php
        $assessment = $assessment ?? null;
        $displayName = $user->name ?? $assessment?->player_name ?? '—';
        $displayEmail = $user->email ?? '—';
        $initial = strtoupper(substr($displayName, 0, 1));
        $player = $player ?? $user->player ?? null;

        $isKacamata = $assessment?->snapshot['is_kacamata'] ?? $user->is_kacamata ?? false;

        function bmiCategory($bmi) {
            if ($bmi < 18.5) return 'Kurus';
            if ($bmi < 25) return 'Normal';
            if ($bmi < 30) return 'Gemuk';
            return 'Obesitas';
        }

        function riskColor($level) {
            $level = strtolower($level ?? '');

            if (in_array($level, ['high', 'tinggi', 'merah'])) {
                return 'bg-red-500';
            }

            if (in_array($level, ['medium', 'sedang', 'kuning'])) {
                return 'bg-amber-500';
            }

            return 'bg-green-600';
        }

        function riskLabel($level) {
            $level = strtolower($level ?? '');

            if (in_array($level, ['high', 'tinggi', 'merah'])) return 'Risiko Tinggi';
            if (in_array($level, ['medium', 'sedang', 'kuning'])) return 'Risiko Sedang';
            if (in_array($level, ['low', 'rendah', 'hijau'])) return 'Risiko Rendah';

            return 'Belum Ada Data';
        }
    @endphp

    <div class="space-y-6 pb-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Profil Saya</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Data pribadi dan status kesehatan Anda sebagai peserta.
                </p>
            </div>

            <a
                href="{{ route('dashboard.player.profil') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors shadow-sm shrink-0"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M20 9A8 8 0 006.34 4.34L4 6.68M4 15a8 8 0 0013.66 4.66L20 17.32"/>
                </svg>
                Refresh
            </a>
        </div>

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            {{-- Left --}}
            <div class="lg:col-span-1 space-y-5">
                {{-- Profile card --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="h-20 bg-gradient-to-r from-[#8a1519] to-[#c21e24]"></div>

                    <div class="px-6 pb-6">
                        <div class="flex justify-center -mt-10 mb-4">
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-[#a81d22] to-[#e53935] flex items-center justify-center text-white text-3xl font-extrabold shadow-md border-4 border-white overflow-hidden">
                                @if($player?->photo_path)
                                    <img
                                        src="{{ str_starts_with($player->photo_path, 'http') ? $player->photo_path : asset('storage/' . $player->photo_path) }}"
                                        alt="Foto profil"
                                        class="w-full h-full object-cover"
                                    >
                                @else
                                    {{ $initial }}
                                @endif
                            </div>
                        </div>

                        <div class="text-center">
                            <h2 class="text-xl font-extrabold text-gray-900">
                                {{ $displayName }}
                            </h2>

                            <p class="text-sm text-gray-500 mt-1">
                                {{ $displayEmail }}
                            </p>

                            <div class="flex items-center justify-center flex-wrap gap-2 mt-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                    Peserta
                                </span>

                                @if($isKacamata)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        👓 Kacamata
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detail akun --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-700 mb-4">Detail Akun</h3>

                    <div class="space-y-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Nama Lengkap</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $displayName }}</p>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Email</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $displayEmail }}</p>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Peran</p>
                            <p class="text-sm font-semibold text-gray-800">Peserta (Player)</p>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Pengguna Kacamata</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $isKacamata ? 'Ya' : 'Tidak' }}</p>
                        </div>

                        @if($player?->contingent)
                            <div class="border-t border-gray-100 pt-3 mt-1">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Kontingen</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $player->contingent->name }}</p>
                            </div>
                        @endif

                        @if($player?->nim_nip)
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">NIM/NIP</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $player->nim_nip }}</p>
                            </div>
                        @endif

                        @if($assessment?->sport_branch)
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Cabang Olahraga</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $assessment->sport_branch }}</p>
                            </div>
                        @endif

                        @if($assessment?->snapshot && isset($assessment->snapshot['age']))
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Usia</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $assessment->snapshot['age'] }} tahun</p>
                            </div>
                        @endif

                        @if($assessment?->snapshot && isset($assessment->snapshot['bmi']))
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">BMI</p>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ number_format($assessment->snapshot['bmi'], 1) }}
                                    ({{ bmiCategory($assessment->snapshot['bmi']) }})
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-100">
                        <a
                            href="{{ route('dashboard.player.profil.edit') }}"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-[#b71c1c] hover:underline"
                        >
                            ✎ Edit Profil →
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right --}}
            <div class="lg:col-span-2 space-y-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#b71c1c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 14h.01M9 17h.01M13 14h3M13 17h3"/>
                        </svg>
                        Status Self-Assessment
                    </h2>

                    @if(!$assessment)
                        <a
                            href="{{ route('dashboard.player.self-assessment') }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#b71c1c] text-white text-sm font-bold rounded-lg hover:bg-[#9b1818] transition-colors shadow-sm"
                        >
                            Mulai Assessment →
                        </a>
                    @endif
                </div>

                @if(!$assessment)
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12 text-center">
                        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-gray-50 flex items-center justify-center text-gray-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 14h.01M13 14h3"/>
                            </svg>
                        </div>

                        <h3 class="text-lg font-bold text-gray-700 mb-2">Belum Ada Assessment</h3>
                        <p class="text-sm text-gray-500 mb-6 max-w-md mx-auto">
                            Anda belum pernah mengisi self-assessment kesehatan. Isi sekarang untuk mengetahui status risiko sebelum bertanding.
                        </p>

                        <a
                            href="{{ route('dashboard.player.self-assessment') }}"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-[#b71c1c] text-white text-sm font-bold rounded-lg hover:bg-[#9b1818] transition-colors shadow-sm"
                        >
                            Isi Self Assessment
                        </a>
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-1">Status Risiko</p>
                                <h3 class="text-2xl font-black text-gray-900">
                                    {{ riskLabel($assessment->risk_lvl ?? $player?->risk_lvl) }}
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Berdasarkan self-assessment terakhir yang sudah kamu isi.
                                </p>
                            </div>

                            <div class="w-14 h-14 rounded-2xl {{ riskColor($assessment->risk_lvl ?? $player?->risk_lvl) }} flex items-center justify-center text-white shadow-sm">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5-3v5c0 5-3.5 9-8 10-4.5-1-8-5-8-10V7l8-4 8 4z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 pt-5 border-t border-gray-100">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Tanggal Assessment</p>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $assessment->created_at ? $assessment->created_at->translatedFormat('d F Y') : '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Status Validasi</p>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $assessment->is_valid ?? false ? 'Valid' : 'Belum divalidasi' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.dashboard>