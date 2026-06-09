<x-layouts.dashboard :roleLabel="'PIC Kontingen'">
    <x-slot:title>Profil Saya</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-pic')
    </x-slot:sidebar>

    <div class="space-y-6 pb-10">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Profil Saya</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Data pribadi Anda sebagai PIC Kontingen.
                </p>
            </div>
            <button onclick="window.location.reload()" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors shadow-sm shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh
            </button>
        </div>

        {{-- Main Grid --}}
        <div class="{{ $assessment ? 'grid grid-cols-1 lg:grid-cols-3 gap-6 items-start' : 'max-w-2xl space-y-5' }}">
            
            {{-- LEFT: Personal Info (1/3) --}}
            <div class="{{ $assessment ? 'lg:col-span-1 space-y-5' : 'space-y-5' }}">
                {{-- Profile card --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    {{-- Top gradient bar --}}
                    <div class="h-24 bg-gradient-to-r from-[#8a1519] to-[#c21e24]"></div>

                    <div class="px-6 pb-6">
                        {{-- Avatar --}}
                        <div class="flex justify-center -mt-16 mb-4">
                            @if($user->player?->photo_path)
                            <div class="w-32 h-32 rounded-2xl shadow-lg border-4 border-white overflow-hidden bg-gray-100 flex items-center justify-center">
                                <img src="{{ $user->player->photo_path }}" alt="{{ $user->name }}" class="w-full h-full object-contain">
                            </div>
                            @else
                            <div class="w-32 h-32 rounded-2xl bg-gradient-to-br from-[#a81d22] to-[#e53935] flex items-center justify-center text-white text-4xl font-extrabold shadow-lg border-4 border-white">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            @endif
                        </div>

                        <div class="text-center">
                            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight leading-tight">
                                {{ $user->name }}
                            </h2>
                            <p class="text-sm text-gray-500 mt-1 flex items-center justify-center gap-1.5">
                                <svg class="text-gray-400 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $user->email }}
                            </p>
                            <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-100 uppercase tracking-wide">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    {{ str_replace('_', ' ', $user->role) }}
                                </span>
                                @if($user->is_kacamata)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                    👓 Kacamata
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detail info card --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-700 mb-4">Detail Akun</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Nama Lengkap</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $user->name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Email</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $user->email }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Peran</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $user->role === 'pic_kontingen' ? 'PIC Kontingen' : $user->role }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Pengguna Kacamata</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $user->is_kacamata ? 'Ya' : 'Tidak' }}</p>
                        </div>

                        @if($contingent)
                        <div class="border-t border-gray-100 pt-3 mt-1">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Kontingen</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $contingent->name }}</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Jumlah Pemain</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $playersCount }} terdaftar</p>
                        </div>
                        @endif
                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-100">
                        <a href="{{ url('/dashboard/pic-kontingen/profil-kontingen') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#b71c1c] hover:underline">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Lihat Profil Kontingen &rarr;
                        </a>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Self-Assessment (2/3) --}}
            @if($assessment)
            <div class="lg:col-span-2 space-y-5">
                {{-- Assessment header --}}
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#b71c1c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Status Self-Assessment
                    </h2>
                </div>

                @php
                    $riskConfig = [
                        'high' => [
                            'label' => 'Risiko Tinggi',
                            'badge' => 'bg-[#B41F2A] text-white',
                            'border' => 'border-[#B41F2A]',
                            'text' => 'text-[#B41F2A]',
                            'bg' => 'bg-red-50',
                            'headerBar' => 'bg-[#B41F2A]',
                            'bar' => 'bg-[#B41F2A]',
                        ],
                        'medium' => [
                            'label' => 'Risiko Sedang',
                            'badge' => 'bg-amber-500 text-white',
                            'border' => 'border-amber-500',
                            'text' => 'text-amber-600',
                            'bg' => 'bg-amber-50',
                            'headerBar' => 'bg-amber-500',
                            'bar' => 'bg-amber-500',
                        ],
                        'low' => [
                            'label' => 'Risiko Rendah',
                            'badge' => 'bg-green-600 text-white',
                            'border' => 'border-green-600',
                            'text' => 'text-green-700',
                            'bg' => 'bg-green-50',
                            'headerBar' => 'bg-green-600',
                            'bar' => 'bg-green-600',
                        ]
                    ];
                    $risk = $riskConfig[$assessment->risk_label] ?? $riskConfig['low'];
                    $isValid = $assessment->isValid();
                    
                    function getDomainColor($score) {
                        if ($score >= 51) return "bg-[#B41F2A]";
                        if ($score >= 26) return "bg-amber-500";
                        return "bg-green-600";
                    }
                    
                    function getBMICat($bmi) {
                        if ($bmi < 18.5) return "Kurus";
                        if ($bmi < 25) return "Normal";
                        if ($bmi < 30) return "Gemuk";
                        return "Obesitas";
                    }
                @endphp

                {{-- Risk Summary Card --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="h-1.5 {{ $risk['headerBar'] }}"></div>
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-3 mb-2">
                                    <span class="rounded-full px-5 py-1.5 text-sm font-extrabold {{ $risk['badge'] }}">
                                        {{ $risk['label'] }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap gap-3 text-xs text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <svg class="text-gray-400 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Diisi: {{ $assessment->created_at->translatedFormat('d M Y, H:i') }}
                                    </span>
                                    <span class="flex items-center gap-1 font-semibold {{ $isValid ? 'text-green-600' : 'text-red-500' }}">
                                        @if($isValid)
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Berlaku hingga {{ $assessment->valid_until ? $assessment->valid_until->translatedFormat('d F Y') : '—' }}
                                        @else
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Kadaluarsa
                                        @endif
                                    </span>
                                </div>

                                @if($assessment->requires_clearance)
                                <div class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-[#B41F2A] bg-red-50 border border-red-200 rounded-lg px-3 py-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Memerlukan clearance medis
                                </div>
                                @endif
                            </div>

                            <a href="{{ url('/dashboard/pic-kontingen/self-assessment/hasil?id='.$assessment->id) }}" class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 border border-gray-200 bg-white text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 hover:border-[#b71c1c] hover:text-[#b71c1c] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg> Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Domain Scores --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Skor Per Domain</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                        @php
                            $domains = [
                                ['label' => 'Kardiovaskular', 'weight' => '35%', 'score' => $assessment->score_cardiovascular ?? 0],
                                ['label' => 'Muskuloskeletal', 'weight' => '30%', 'score' => $assessment->score_musculoskeletal ?? 0],
                                ['label' => 'Kesiapan Akut', 'weight' => '20%', 'score' => $assessment->score_acute_readiness ?? 0],
                                ['label' => 'Psikososial', 'weight' => '15%', 'score' => $assessment->score_psychosocial ?? 0],
                            ];
                        @endphp
                        
                        @foreach($domains as $domain)
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-medium text-gray-600">{{ $domain['label'] }}</span>
                                    <span class="text-[10px] text-gray-400">({{ $domain['weight'] }})</span>
                                </div>
                                <span class="text-xs font-bold text-gray-700">{{ number_format($domain['score'], 1) }}</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full rounded-full {{ getDomainColor($domain['score']) }}" style="width: {{ min($domain['score'], 100) }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <p class="mt-3 text-[10px] text-gray-400">
                        Interpretasi: 0–25 Rendah &middot; 26–50 Sedang &middot; 51–100 Tinggi
                    </p>
                </div>

                {{-- Flags --}}
                @php
                    $redFlags = $assessment->red_flags ?? [];
                    $yellowFlags = $assessment->yellow_flags ?? [];
                @endphp

                @if(count($redFlags) > 0 || count($yellowFlags) > 0)
                <div class="grid sm:grid-cols-2 gap-4">
                    {{-- Red flags --}}
                    @if(count($redFlags) > 0)
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-5">
                        <h3 class="text-sm font-bold text-[#B41F2A] mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Red Flag ({{ count($redFlags) }})
                        </h3>
                        <div class="space-y-2">
                            @foreach($redFlags as $f)
                            <div class="bg-white rounded-lg border border-red-100 px-3 py-2">
                                <p class="text-xs font-bold text-gray-800">{{ is_string($f) ? $f : ($f['text'] ?? '') }}</p>
                                @if(is_array($f) && !empty($f['reason']))
                                <p class="text-xs text-[#B41F2A] mt-0.5">{{ $f['reason'] }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Yellow flags --}}
                    @if(count($yellowFlags) > 0)
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                        <h3 class="text-sm font-bold text-amber-700 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Perhatian ({{ count($yellowFlags) }})
                        </h3>
                        <div class="space-y-2">
                            @foreach($yellowFlags as $f)
                            <div class="bg-white rounded-lg border border-amber-100 px-3 py-2">
                                <p class="text-xs font-bold text-gray-800">{{ is_string($f) ? $f : ($f['text'] ?? '') }}</p>
                                @if(is_array($f) && !empty($f['reason']))
                                <p class="text-xs text-amber-700 mt-0.5">{{ $f['reason'] }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                {{-- No flags --}}
                @if(count($redFlags) == 0 && count($yellowFlags) == 0)
                <div class="bg-green-50 border border-green-200 rounded-2xl p-5 flex items-center gap-3">
                    <svg class="w-6 h-6 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-sm font-bold text-green-800">Tidak Ada Red Flag atau Yellow Flag</p>
                        <p class="text-xs text-green-700 mt-0.5">Tidak ditemukan indikator risiko signifikan.</p>
                    </div>
                </div>
                @endif

                {{-- Medical Review & Snapshot --}}
                <div class="grid sm:grid-cols-2 gap-4">
                    {{-- Medical review status --}}
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                        <h3 class="text-sm font-bold text-gray-700 mb-3">Status Review Medis</h3>
                        @if($assessment->reviewed_at)
                        <div class="space-y-3">
                            <div class="flex items-center gap-2 rounded-lg px-3 py-2.5 {{ $assessment->is_allowed_to_play ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                @if($assessment->is_allowed_to_play)
                                    <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @else
                                    <svg class="w-4 h-4 text-[#B41F2A] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                                <p class="text-sm font-bold {{ $assessment->is_allowed_to_play ? 'text-green-700' : 'text-[#B41F2A]' }}">
                                    {{ $assessment->is_allowed_to_play ? 'Diizinkan Bermain' : 'Tidak Diizinkan' }}
                                </p>
                            </div>
                            @if($assessment->medical_notes)
                            <p class="text-xs text-gray-600 bg-gray-50 rounded-lg p-3 border border-gray-100 italic leading-relaxed">
                                "{{ $assessment->medical_notes }}"
                            </p>
                            @endif
                            <p class="text-[10px] text-gray-400 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Direview: {{ $assessment->reviewed_at->translatedFormat('d M Y, H:i') }}
                            </p>
                        </div>
                        @else
                        <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3 text-sm text-blue-600">
                            Review medis belum dilakukan oleh panitia.
                        </div>
                        @endif
                    </div>

                    {{-- Snapshot data --}}
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                        <h3 class="text-sm font-bold text-gray-700 mb-3">Data Fisik Saat Ini</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Usia</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $assessment->age_snapshot ?? '—' }} tahun</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">BMI</p>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ number_format($assessment->bmi_snapshot ?? 0, 1) }} 
                                    ({{ getBMICat($assessment->bmi_snapshot ?? 0) }})
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Kacamata Olahraga</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $assessment->is_kacamata_snapshot ? 'Ya' : 'Tidak' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-0.5">Cabang Olahraga</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $assessment->sport_branch_snapshot ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recommendation --}}
                @if($assessment->recommendation)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#b71c1c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg> Rekomendasi Sistem
                    </h3>
                    <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line bg-gray-50 rounded-xl p-4 border border-gray-100">
                        {{ $assessment->recommendation }}
                    </p>
                </div>
                @endif

                {{-- Expired warning --}}
                @if(!$isValid)
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-3">
                    <svg class="text-amber-600 w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <p class="text-sm font-bold text-amber-800">Assessment Sudah Kadaluarsa</p>
                        <p class="text-xs text-amber-700 mt-1">
                            Masa berlaku assessment Anda telah habis. Silakan isi ulang untuk mendapatkan evaluasi kesehatan terbaru sebelum bertanding.
                        </p>
                        <a href="{{ route('dashboard.pic.self-assessment.index') }}" class="inline-block mt-3 text-xs font-bold text-amber-700 underline">
                            Isi Ulang Sekarang &rarr;
                        </a>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
