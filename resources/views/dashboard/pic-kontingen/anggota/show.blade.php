<x-layouts.dashboard :roleLabel="'PIC Kontingen'">
    <x-slot:title>Detail Anggota</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-pic')
    </x-slot:sidebar>

    <div class="space-y-6 pb-10">
        {{-- Header & Back Button --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard.pic.anggota') }}" class="p-2 text-gray-400 hover:text-brand hover:bg-red-50 rounded-lg transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Anggota Kontingen</h1>
                <p class="text-gray-500 text-sm mt-1">Informasi lengkap profil dan status anggota.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Kolom Kiri: Profil --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-32 h-32 rounded-full overflow-hidden bg-gray-100 border-4 border-white shadow-lg mb-4">
                            @if($member->photo_path)
                                <img src="{{ $member->photo_path }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                            @endif
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $member->name }}</h2>
                        <p class="text-gray-500 text-sm mb-4">{{ $member->user ? $member->user->email : '-' }}</p>
                        
                        @if($member->risk_lvl === 'low')
                            <span class="inline-flex items-center bg-emerald-50 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full border border-emerald-200">Risiko Rendah</span>
                        @elseif($member->risk_lvl === 'medium')
                            <span class="inline-flex items-center bg-orange-50 text-orange-700 text-xs font-semibold px-3 py-1 rounded-full border border-orange-200">Risiko Sedang</span>
                        @elseif($member->risk_lvl === 'high')
                            <span class="inline-flex items-center bg-red-50 text-red-700 text-xs font-semibold px-3 py-1 rounded-full border border-red-200">Risiko Tinggi</span>
                        @else
                            <span class="inline-flex items-center bg-gray-50 text-gray-500 text-xs font-medium px-3 py-1 rounded-full border border-gray-200">Belum Mengisi Assessment</span>
                        @endif
                    </div>

                    <div class="mt-8 space-y-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">NIM / NIP</p>
                            <p class="text-gray-900 font-medium">{{ $member->nim_nip ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Status Civitas</p>
                            <p class="text-gray-900 font-medium">
                                {{ $member->employee_status === 'student' ? 'Mahasiswa' : ($member->employee_status === 'employee' ? 'Karyawan / Dosen' : ($member->employee_status ?: '-')) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Lokasi Kerja / Kampus</p>
                            <p class="text-gray-900 font-medium">{{ $member->work_location ?: '-' }}</p>
                        </div>
                    </div>
                </div>
                
                {{-- Status Kelengkapan --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Status Kelengkapan</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Foto Profil</span>
                            @if($member->photo_path)
                                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Identitas Pribadi</span>
                            @if($member->nim_nip && $member->employee_status && $member->work_location)
                                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Self Assessment</span>
                            @if($assessment)
                                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Self Assessment & Medis --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-gray-900">Hasil Self Assessment</h3>
                        @if($assessment)
                            <span class="text-xs text-gray-500">Terakhir diisi: {{ $assessment->created_at->translatedFormat('d F Y H:i') }}</span>
                        @endif
                    </div>

                    @if($assessment)
                        <div class="space-y-6">
                            {{-- Tinjauan Medis Panitia --}}
                            <div class="bg-blue-50 border border-blue-100 rounded-xl p-5">
                                <h4 class="text-sm font-bold text-blue-900 mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    Status Kelayakan Bermain
                                </h4>
                                
                                @if($assessment->reviewed_at)
                                    <div class="flex items-center gap-3 mb-3">
                                        @if($assessment->is_allowed_to_play)
                                            <span class="inline-flex items-center bg-emerald-100 text-emerald-800 text-sm font-bold px-3 py-1.5 rounded-lg">
                                                DIIZINKAN BERMAIN
                                            </span>
                                        @else
                                            <span class="inline-flex items-center bg-red-100 text-red-800 text-sm font-bold px-3 py-1.5 rounded-lg">
                                                TIDAK DIIZINKAN
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-blue-800"><span class="font-semibold">Catatan Medis:</span> {{ $assessment->medical_notes ?: 'Tidak ada catatan.' }}</p>
                                    <p class="text-xs text-blue-600 mt-2">Ditinjau pada: {{ $assessment->reviewed_at->translatedFormat('d F Y H:i') }}</p>
                                @else
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center bg-amber-100 text-amber-800 text-sm font-bold px-3 py-1.5 rounded-lg">
                                            MENUNGGU REVIEW PANITIA
                                        </span>
                                    </div>
                                    <p class="text-sm text-blue-800 mt-2">Panitia medis belum memberikan keputusan kelayakan bermain.</p>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="border border-gray-100 rounded-xl p-4 bg-gray-50">
                                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Cabang Olahraga Saat Tes</p>
                                    <p class="text-gray-900 font-medium">{{ $assessment->sport_branch_snapshot ?: '-' }}</p>
                                </div>
                                <div class="border border-gray-100 rounded-xl p-4 bg-gray-50">
                                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Rekomendasi Sistem</p>
                                    <p class="text-gray-900 font-medium">{{ $assessment->recommendation ?: '-' }}</p>
                                </div>
                            </div>
                            
                            {{-- Breakdowns --}}
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 mb-3 border-b border-gray-100 pb-2">Rincian Jawaban & Analisis</h4>
                                <div class="space-y-3">
                                    <div class="flex items-start justify-between border-b border-gray-50 pb-3">
                                        <span class="text-sm text-gray-600 w-1/3">Riwayat Cedera:</span>
                                        <span class="text-sm font-medium text-gray-900 w-2/3 text-right">{{ $assessment->injury_history ?: 'Tidak ada' }}</span>
                                    </div>
                                    <div class="flex items-start justify-between border-b border-gray-50 pb-3">
                                        <span class="text-sm text-gray-600 w-1/3">Kondisi Saat Ini:</span>
                                        <span class="text-sm font-medium text-gray-900 w-2/3 text-right">{{ $assessment->current_condition ?: '-' }}</span>
                                    </div>
                                    <div class="flex items-start justify-between border-b border-gray-50 pb-3">
                                        <span class="text-sm text-gray-600 w-1/3">Skor Nyeri:</span>
                                        <span class="text-sm font-medium text-gray-900 w-2/3 text-right">{{ $assessment->pain_score ?? '-' }} / 10</span>
                                    </div>
                                </div>
                            </div>
                            
                            @if(is_array($assessment->red_flags) && count($assessment->red_flags) > 0)
                            <div class="bg-red-50 rounded-xl p-4 border border-red-100">
                                <h4 class="text-sm font-bold text-red-800 mb-2">Red Flags Terdeteksi:</h4>
                                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                    @foreach($assessment->red_flags as $flag)
                                        <li>{{ $flag }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Belum Mengisi Self Assessment</h3>
                            <p class="text-gray-500 mt-1 max-w-sm mx-auto">Anggota ini belum mengisi kuesioner Self Assessment. Sistem belum dapat menentukan tingkat risiko dan rekomendasi kelayakan.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
