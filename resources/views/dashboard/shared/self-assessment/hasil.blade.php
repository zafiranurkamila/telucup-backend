@php
    $isPlayer = request()->user()->role === 'player';
    $roleLabel = $isPlayer ? 'Player' : 'PIC Kontingen';
    
    // ID formatting
    $formattedId = "#PLR-" . date('Y-m', strtotime($data['created_at'])) . "-" . str_pad($data['player_id'] ?? 1, 3, '0', STR_PAD_LEFT);
@endphp

<x-layouts.dashboard :roleLabel="$roleLabel">
    <x-slot:title>Hasil Self Assessment</x-slot:title>

    <x-slot:sidebar>
        @if($isPlayer)
            @include('partials.sidebar-player')
        @else
            @include('partials.sidebar-pic')
        @endif
    </x-slot:sidebar>

<main class="min-h-screen bg-white font-sans text-gray-800 pb-12">
    <div class="mx-auto max-w-7xl px-4 py-8 lg:px-8 lg:py-10">
        
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-8 gap-4 border-b border-gray-100 pb-6">
            <div>
                <div class="flex flex-wrap items-center gap-3 text-xs font-bold text-gray-400 mb-3">
                    <span>{{ \Carbon\Carbon::parse($data['created_at'])->translatedFormat('d M Y, H:i') }} WIB</span>
                </div>
                <h1 class="text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight">
                    Hasil Self-Assessment — <span class="text-[#B41F2A]">{{ $data['player_name'] ?? 'Bagus Setiawan' }}</span>
                </h1>
            </div>
            
            <div class="flex items-center gap-3 shrink-0">
                <button class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Export PDF
                </button>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid gap-8 lg:grid-cols-[1.4fr_1fr] items-start">
            
            {{-- Left Column --}}
            <div class="space-y-8">
                
                {{-- Risk Card --}}
                <div class="bg-white border border-gray-100 rounded-3xl p-6 md:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden">
                    @php
                        $riskLabel = strtolower($data['risk_label'] ?? 'low');
                        $riskColor = $riskLabel === 'high' ? '#B41F2A' : ($riskLabel === 'medium' ? '#f59e0b' : '#10b981');
                        $riskBg = $riskLabel === 'high' ? 'bg-[#B41F2A]' : ($riskLabel === 'medium' ? 'bg-amber-500' : 'bg-green-500');
                        $riskText = $riskLabel === 'high' ? 'text-[#B41F2A]' : ($riskLabel === 'medium' ? 'text-amber-600' : 'text-green-600');
                        $riskName = $riskLabel === 'high' ? 'High Risk' : ($riskLabel === 'medium' ? 'Medium Risk' : 'Low Risk');
                        $riskDesc = $riskLabel === 'high' ? 'Sangat Berisiko Cedera' : ($riskLabel === 'medium' ? 'Risiko Cedera Menengah' : 'Aman untuk Bermain');
                    @endphp
                    <div class="absolute top-0 inset-x-0 h-1.5 {{ $riskBg }}"></div>
                    
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-8 mt-2">
                        {{-- Details --}}
                        <div class="flex-1 text-center sm:text-left">
                            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mb-4">
                                <span class="{{ $riskBg }} text-white px-4 py-1.5 rounded-full text-sm font-extrabold shadow-sm">
                                    {{ $riskName }}
                                </span>
                                <span class="text-sm font-bold {{ $riskText }}">{{ $riskDesc }}</span>
                            </div>
                            
                            <h2 class="text-xl font-bold text-gray-900 leading-snug">
                                {{ $data['recommendation'] ?? 'Pemain tidak direkomendasikan untuk mengikuti aktivitas intensitas tinggi.' }}
                            </h2>
                        </div>
                    </div>
                </div>

                {{-- Model Explanation --}}
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 mb-1">
                            <svg class="w-5 h-5 text-[#B41F2A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            Detail Analisis Skor
                        </h3>
                        <p class="text-sm text-gray-500 font-medium">Sistem menganalisis data kualitatif asesmen dan riwayat pemain untuk menentukan bobot risiko.</p>
                    </div>

                    <div class="space-y-6">
                        @php
                            $domains = [
                                ['key' => 'cardiovascular', 'label' => 'Kardiovaskular'],
                                ['key' => 'musculoskeletal', 'label' => 'Muskuloskeletal'],
                                ['key' => 'acute_readiness', 'label' => 'Kesiapan Akut'],
                                ['key' => 'psychosocial', 'label' => 'Psikososial'],
                            ];
                        @endphp
                        @foreach($domains as $domain)
                            @php $score = $data['domain_scores'][$domain['key']] ?? 0; @endphp
                            <div>
                                <div class="flex justify-between text-sm font-bold text-gray-800 mb-2.5">
                                    <span>{{ $domain['label'] }}</span>
                                    <span class="text-gray-500">{{ number_format($score, 1) }} / 100</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-1000 {{ $score >= 80 ? 'bg-green-500' : ($score >= 50 ? 'bg-amber-500' : 'bg-[#B41F2A]') }}" style="width: {{ $score }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pt-2 gap-3 border-t border-gray-100 mt-6 pt-4">
                        <span class="text-xs text-gray-400 italic font-medium">Model Confidence: <strong class="text-gray-600 font-bold">{{ $data['confidence_score'] ?? 87 }}%</strong> (Kualitas data: Tinggi)</span>
                    </div>
                </div>

            </div>

            {{-- Right Column --}}
            <div class="space-y-6">
                
                {{-- Player Card --}}
                <div class="bg-[#fafafa] rounded-[2rem] p-6 lg:p-8 border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-5 mb-8">
                        <div class="w-16 h-16 rounded-full bg-gray-200 overflow-hidden shrink-0 shadow-md">
                            <img src="{{ $data['player_photo'] ?? 'https://ui-avatars.com/api/?name='.urlencode($data['player_name'] ?? 'Bagus').'&background=1f2937&color=fff' }}" alt="Avatar" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h3 class="font-black text-gray-900 text-xl tracking-tight">{{ $data['player_name'] ?? 'Bagus Setiawan' }}</h3>
                            <p class="text-sm text-gray-600 font-semibold mt-0.5">{{ $data['contingent'] ?? '-' }} • {{ $data['sport_branch'] ?? '-' }}</p>
                            <p class="text-xs text-[#B41F2A] font-extrabold mt-1 tracking-wider">ID: {{ $formattedId }}</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-3xl p-5 md:p-6 grid grid-cols-2 gap-y-6 gap-x-4 border border-gray-100 shadow-[0_2px_10px_rgb(0,0,0,0.02)]">
                        <div>
                            <p class="text-[9px] font-extrabold text-gray-400 uppercase tracking-widest mb-1.5">Usia</p>
                            <p class="text-sm font-bold text-gray-900">{{ $data['snapshot']['age'] ?? '21' }} Tahun</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] font-extrabold text-gray-400 uppercase tracking-widest mb-1.5">Posisi</p>
                            <p class="text-sm font-bold text-gray-900">-</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-extrabold text-gray-400 uppercase tracking-widest mb-1.5">Terakhir Latihan</p>
                            <p class="text-sm font-bold text-[#B41F2A]">-</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] font-extrabold text-gray-400 uppercase tracking-widest mb-1.5">Riwayat Cedera</p>
                            <p class="text-sm font-bold text-gray-900 truncate" title="{{ $data['injury_history'] }}">{{ \Illuminate\Support\Str::limit($data['injury_history'], 20) }}</p>
                        </div>
                    </div>
                </div>

                {{-- Summary List --}}
                <div class="bg-[#fafafa] rounded-[2rem] p-6 lg:p-8 border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-7">
                        <h4 class="font-bold text-gray-900 text-sm">Ringkasan Gejala Utama</h4>
                        <a href="#" class="text-[10px] font-extrabold text-[#B41F2A] uppercase hover:underline tracking-wider">Unduh CSV</a>
                    </div>
                    
                    <div class="space-y-5">
                        @php
                            $allFlags = array_merge(
                                array_map(fn($f) => array_merge(is_array($f) ? $f : ['text' => $f], ['type' => 'red']), is_array($data['red_flags'] ?? []) ? $data['red_flags'] : []),
                                array_map(fn($f) => array_merge(is_array($f) ? $f : ['text' => $f], ['type' => 'yellow']), is_array($data['yellow_flags'] ?? []) ? $data['yellow_flags'] : [])
                            );
                        @endphp
                        @forelse($allFlags as $flag)
                            <div class="flex gap-4 pb-5 border-b border-gray-200 border-dashed last:border-0 last:pb-0">
                                <div class="w-2.5 h-2.5 rounded-full {{ $flag['type'] === 'red' ? 'bg-[#B41F2A]' : 'bg-amber-500' }} mt-1 shrink-0"></div>
                                <div>
                                    <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest mb-1">
                                        {{ $flag['type'] === 'red' ? 'Peringatan Utama' : 'Perlu Perhatian' }}
                                    </p>
                                    <p class="text-sm font-bold {{ $flag['type'] === 'red' ? 'text-[#B41F2A]' : 'text-amber-600' }}">
                                        {{ is_array($flag) ? ($flag['text'] ?? '') : $flag }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 italic font-medium">Tidak ada gejala berisiko tinggi yang terdeteksi.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Audit Log --}}
                <div class="bg-[#fafafa] rounded-[2rem] p-6 lg:p-8 border border-gray-100 shadow-sm">
                    <h4 class="font-extrabold text-gray-400 text-[10px] uppercase tracking-widest flex items-center gap-2.5 mb-6">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Audit Log
                    </h4>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="text-xs font-bold text-gray-800">Analisis Selesai</span>
                            </div>
                            <span class="text-[11px] text-gray-400 font-bold">14:32</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-200 pt-4 border-dashed">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="text-xs font-bold text-gray-800">PDF Laporan Diunduh</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] text-gray-400 font-bold">14:35</span>
                                <div class="w-1.5 h-1.5 rounded-full bg-gray-300"></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-200 pt-4 border-dashed">
                            <div class="flex items-center gap-3">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="text-xs font-bold text-gray-800">Email ke Coach Sent</span>
                            </div>
                            <span class="text-[11px] text-gray-400 font-bold">14:40</span>
                        </div>
                    </div>
                </div>

            </div>
            
        </div>
    </div>
</main>
</x-layouts.dashboard>
