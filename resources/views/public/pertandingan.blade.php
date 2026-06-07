<x-layout-public>
    <x-slot:title>Detail Pertandingan</x-slot:title>

    @php
        $match = [
            'id' => $id ?? 1,
            'round_name' => 'Semifinal',
            'match_number' => 1,
            'status' => 'scheduled',
            'match_date' => '2026-06-10',
            'match_time' => '15:30 WIB',
            'location' => 'Lapangan Utama',
            'referee_name' => 'TBD',
            'score_a' => null,
            'score_b' => null,
            'team_a' => [
                'contingent' => [
                    'name' => 'FIF',
                    'image_url' => null,
                    'cloudinary_public_id' => null,
                ],
                'players' => [
                    ['id' => 1, 'name' => 'Alya Putri', 'nim_nip' => '1301230001', 'photo_path' => null, 'risk_lvl' => 'rendah'],
                    ['id' => 2, 'name' => 'Nadia Rahma', 'nim_nip' => '1301230002', 'photo_path' => null, 'risk_lvl' => 'sedang'],
                ],
            ],
            'team_b' => [
                'contingent' => [
                    'name' => 'FTE',
                    'image_url' => null,
                    'cloudinary_public_id' => null,
                ],
                'players' => [
                    ['id' => 3, 'name' => 'Zaskia Salsabila', 'nim_nip' => '1301230003', 'photo_path' => null, 'risk_lvl' => 'rendah'],
                    ['id' => 4, 'name' => 'Siti Inaya', 'nim_nip' => '1301230004', 'photo_path' => null, 'risk_lvl' => 'tinggi'],
                ],
            ],
        ];

        $getRiskBadge = function ($level) {
            $level = strtolower($level ?? '');

            if (in_array($level, ['high', 'merah', 'tinggi'])) {
                return 'bg-red-100 text-red-700 border-red-200';
            }

            if (in_array($level, ['medium', 'kuning', 'sedang'])) {
                return 'bg-yellow-100 text-yellow-700 border-yellow-200';
            }

            if (in_array($level, ['low', 'hijau', 'rendah'])) {
                return 'bg-green-100 text-green-700 border-green-200';
            }

            return 'bg-gray-100 text-gray-700 border-gray-200';
        };

        $getStatusBadge = function ($status) {
            return match ($status) {
                'live' => 'bg-red-100 text-red-600 border-red-200 animate-pulse',
                'finished' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                default => 'bg-gray-100 text-gray-600 border-gray-200',
            };
        };

        $isLowRisk = function ($riskLvl) {
            return in_array(strtolower($riskLvl ?? ''), ['hijau', 'rendah', 'low', 'not_yet']);
        };

        $teamA = $match['team_a'];
        $teamB = $match['team_b'];
        $scoreA = $match['score_a'] ?? 0;
        $scoreB = $match['score_b'] ?? 0;

        $formattedDate = $match['match_date']
            ? \Carbon\Carbon::parse($match['match_date'])->translatedFormat('l, d F Y')
            : '-';
    @endphp

    <div class="min-h-screen bg-[#f4f7f6]">
        {{-- Header --}}
        <header class="bg-white border-b border-gray-200 sticky top-0 z-10 shadow-sm">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button onclick="history.back()" class="p-2 hover:bg-gray-100 rounded-full transition-colors text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7 7-7-7 7-7" />
                        </svg>
                    </button>

                    <div>
                        <h1 class="font-bold text-gray-800 text-lg leading-tight">Detail Pertandingan</h1>
                        <p class="text-xs text-gray-500">
                            Cabang Olahraga • {{ $match['round_name'] ?: 'Round' }}
                        </p>
                    </div>
                </div>

                <div class="px-3 py-1 rounded-full text-xs font-bold border uppercase tracking-wider {{ $getStatusBadge($match['status']) }}">
                    {{ $match['status'] === 'live' ? 'LIVE NOW' : ($match['status'] ?: 'TBD') }}
                </div>
            </div>
        </header>

        <main class="max-w-[1200px] mx-auto px-4 sm:px-6 py-8 space-y-6">
            {{-- Match Info & Scoreboard Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 md:p-8 bg-gradient-to-b from-gray-50 to-white flex flex-col md:flex-row items-center justify-center gap-8 md:gap-16 border-b border-gray-100">
                    {{-- Team A --}}
                    <div class="flex flex-col items-center flex-1 w-full max-w-[200px]">
                        <div class="w-24 h-24 rounded-full bg-white shadow-md border border-gray-100 flex items-center justify-center text-3xl font-black text-gray-400 mb-4 overflow-hidden">
                            @if($teamA['contingent']['image_url'])
                                <img src="{{ $teamA['contingent']['image_url'] }}" alt="{{ $teamA['contingent']['name'] }}" class="w-full h-full object-cover">
                            @else
                                <span>{{ substr($teamA['contingent']['name'] ?? '?', 0, 1) }}</span>
                            @endif
                        </div>

                        <h2 class="text-xl font-bold text-gray-800 text-center">
                            {{ $teamA['contingent']['name'] ?? 'TBD' }}
                        </h2>
                    </div>

                    {{-- Score --}}
                    <div class="flex flex-col items-center justify-center px-4">
                        <div class="text-[10px] font-bold text-gray-400 tracking-[0.2em] mb-2 uppercase">Score</div>

                        <div class="flex items-center gap-6">
                            <span class="text-5xl md:text-6xl font-black {{ $match['status'] === 'scheduled' ? 'text-gray-300' : 'text-gray-900' }}">
                                {{ $match['status'] === 'scheduled' ? '-' : $scoreA }}
                            </span>

                            <span class="text-2xl font-bold text-gray-300">VS</span>

                            <span class="text-5xl md:text-6xl font-black {{ $match['status'] === 'scheduled' ? 'text-gray-300' : 'text-gray-900' }}">
                                {{ $match['status'] === 'scheduled' ? '-' : $scoreB }}
                            </span>
                        </div>
                    </div>

                    {{-- Team B --}}
                    <div class="flex flex-col items-center flex-1 w-full max-w-[200px]">
                        <div class="w-24 h-24 rounded-full bg-white shadow-md border border-gray-100 flex items-center justify-center text-3xl font-black text-gray-400 mb-4 overflow-hidden">
                            @if($teamB['contingent']['image_url'])
                                <img src="{{ $teamB['contingent']['image_url'] }}" alt="{{ $teamB['contingent']['name'] }}" class="w-full h-full object-cover">
                            @else
                                <span>{{ substr($teamB['contingent']['name'] ?? '?', 0, 1) }}</span>
                            @endif
                        </div>

                        <h2 class="text-xl font-bold text-gray-800 text-center">
                            {{ $teamB['contingent']['name'] ?? 'TBD' }}
                        </h2>
                    </div>
                </div>

                {{-- Info Grid --}}
                <div class="grid grid-cols-2 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                    <div class="p-4 flex flex-col items-center justify-center text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M3 11h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
                        </svg>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Tanggal</p>
                        <p class="font-medium text-gray-800 mt-1">{{ $formattedDate }}</p>
                    </div>

                    <div class="p-4 flex flex-col items-center justify-center text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z" />
                        </svg>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Waktu</p>
                        <p class="font-medium text-gray-800 mt-1">{{ $match['match_time'] ?? '-' }}</p>
                    </div>

                    <div class="p-4 flex flex-col items-center justify-center text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z" />
                            <circle cx="12" cy="10" r="2.5" />
                        </svg>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Lokasi</p>
                        <p class="font-medium text-gray-800 mt-1">{{ $match['location'] ?? '-' }}</p>
                    </div>

                    <div class="p-4 flex flex-col items-center justify-center text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM4 21a8 8 0 0 1 16 0" />
                        </svg>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Wasit</p>
                        <p class="font-medium text-gray-800 mt-1">{{ $match['referee_name'] ?? 'TBD' }}</p>
                    </div>
                </div>
            </div>

            {{-- Player Lists --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach([
                    ['team' => $teamA, 'accent' => 'blue'],
                    ['team' => $teamB, 'accent' => 'red'],
                ] as $item)
                    @php
                        $team = $item['team'];
                        $accent = $item['accent'];

                        $bgClass = $accent === 'blue'
                            ? 'bg-blue-50 text-blue-600 border-blue-100'
                            : 'bg-red-50 text-red-600 border-red-100';

                        $barClass = $accent === 'blue'
                            ? 'bg-blue-500'
                            : 'bg-red-500';

                        $teamName = $team['contingent']['name'] ?? 'TBD';
                        $players = $team['players'] ?? [];
                    @endphp

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between gap-4">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2 overflow-hidden">
                                <div class="w-2 h-6 {{ $barClass }} rounded-full shrink-0"></div>
                                <span class="truncate" title="Daftar Pemain {{ $teamName }}">
                                    Daftar Pemain {{ $teamName }}
                                </span>
                            </h3>

                            <span class="text-xs font-bold text-gray-500 bg-gray-200 px-2.5 py-1 rounded-full shrink-0">
                                {{ count($players) }} Pemain
                            </span>
                        </div>

                        <ul class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                            @forelse($players as $player)
                                <li class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full {{ $bgClass }} flex items-center justify-center font-bold text-sm overflow-hidden border">
                                            @if(!empty($player['photo_path']))
                                                <img src="{{ $player['photo_path'] }}" alt="{{ $player['name'] }}" class="w-full h-full object-cover">
                                            @else
                                                {{ substr($player['name'] ?? '?', 0, 1) }}
                                            @endif
                                        </div>

                                        <div>
                                            <p class="font-semibold text-gray-800 flex items-center gap-2">
                                                {{ $player['name'] }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $player['nim_nip'] ?? '-' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-end">
                                        @if(!empty($player['risk_lvl']))
                                            <span class="text-[10px] px-2 py-0.5 rounded-full border font-semibold flex items-center gap-1 {{ $getRiskBadge($player['risk_lvl']) }}">
                                                @if($isLowRisk($player['risk_lvl']))
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5-3v5c0 5-3.5 9-8 10-4.5-1-8-5-8-10V7l8-4 8 4Z" />
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M20 7v5c0 5-3.5 9-8 10-4.5-1-8-5-8-10V7l8-4 8 4Z" />
                                                    </svg>
                                                @endif

                                                Risiko: {{ $player['risk_lvl'] }}
                                            </span>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li class="p-8 text-center text-gray-500 text-sm">
                                    Tidak ada pemain terdaftar.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                @endforeach
            </div>
        </main>
    </div>
</x-layout-public><x-layout-public>
    <x-slot:title>Detail Pertandingan</x-slot:title>

    @php
        $match = [
            'id' => $id ?? 1,
            'round_name' => 'Semifinal',
            'match_number' => 1,
            'status' => 'scheduled',
            'match_date' => '2026-06-10',
            'match_time' => '15:30 WIB',
            'location' => 'Lapangan Utama',
            'referee_name' => 'TBD',
            'score_a' => null,
            'score_b' => null,
            'team_a' => [
                'contingent' => [
                    'name' => 'FIF',
                    'image_url' => null,
                    'cloudinary_public_id' => null,
                ],
                'players' => [
                    ['id' => 1, 'name' => 'Alya Putri', 'nim_nip' => '1301230001', 'photo_path' => null, 'risk_lvl' => 'rendah'],
                    ['id' => 2, 'name' => 'Nadia Rahma', 'nim_nip' => '1301230002', 'photo_path' => null, 'risk_lvl' => 'sedang'],
                ],
            ],
            'team_b' => [
                'contingent' => [
                    'name' => 'FTE',
                    'image_url' => null,
                    'cloudinary_public_id' => null,
                ],
                'players' => [
                    ['id' => 3, 'name' => 'Zaskia Salsabila', 'nim_nip' => '1301230003', 'photo_path' => null, 'risk_lvl' => 'rendah'],
                    ['id' => 4, 'name' => 'Siti Inaya', 'nim_nip' => '1301230004', 'photo_path' => null, 'risk_lvl' => 'tinggi'],
                ],
            ],
        ];

        $getRiskBadge = function ($level) {
            $level = strtolower($level ?? '');

            if (in_array($level, ['high', 'merah', 'tinggi'])) {
                return 'bg-red-100 text-red-700 border-red-200';
            }

            if (in_array($level, ['medium', 'kuning', 'sedang'])) {
                return 'bg-yellow-100 text-yellow-700 border-yellow-200';
            }

            if (in_array($level, ['low', 'hijau', 'rendah'])) {
                return 'bg-green-100 text-green-700 border-green-200';
            }

            return 'bg-gray-100 text-gray-700 border-gray-200';
        };

        $getStatusBadge = function ($status) {
            return match ($status) {
                'live' => 'bg-red-100 text-red-600 border-red-200 animate-pulse',
                'finished' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                default => 'bg-gray-100 text-gray-600 border-gray-200',
            };
        };

        $isLowRisk = function ($riskLvl) {
            return in_array(strtolower($riskLvl ?? ''), ['hijau', 'rendah', 'low', 'not_yet']);
        };

        $teamA = $match['team_a'];
        $teamB = $match['team_b'];
        $scoreA = $match['score_a'] ?? 0;
        $scoreB = $match['score_b'] ?? 0;

        $formattedDate = $match['match_date']
            ? \Carbon\Carbon::parse($match['match_date'])->translatedFormat('l, d F Y')
            : '-';
    @endphp

    <div class="min-h-screen bg-[#f4f7f6]">
        {{-- Header --}}
        <header class="bg-white border-b border-gray-200 sticky top-0 z-10 shadow-sm">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button onclick="history.back()" class="p-2 hover:bg-gray-100 rounded-full transition-colors text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7 7-7-7 7-7" />
                        </svg>
                    </button>

                    <div>
                        <h1 class="font-bold text-gray-800 text-lg leading-tight">Detail Pertandingan</h1>
                        <p class="text-xs text-gray-500">
                            Cabang Olahraga • {{ $match['round_name'] ?: 'Round' }}
                        </p>
                    </div>
                </div>

                <div class="px-3 py-1 rounded-full text-xs font-bold border uppercase tracking-wider {{ $getStatusBadge($match['status']) }}">
                    {{ $match['status'] === 'live' ? 'LIVE NOW' : ($match['status'] ?: 'TBD') }}
                </div>
            </div>
        </header>

        <main class="max-w-[1200px] mx-auto px-4 sm:px-6 py-8 space-y-6">
            {{-- Match Info & Scoreboard Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 md:p-8 bg-gradient-to-b from-gray-50 to-white flex flex-col md:flex-row items-center justify-center gap-8 md:gap-16 border-b border-gray-100">
                    {{-- Team A --}}
                    <div class="flex flex-col items-center flex-1 w-full max-w-[200px]">
                        <div class="w-24 h-24 rounded-full bg-white shadow-md border border-gray-100 flex items-center justify-center text-3xl font-black text-gray-400 mb-4 overflow-hidden">
                            @if($teamA['contingent']['image_url'])
                                <img src="{{ $teamA['contingent']['image_url'] }}" alt="{{ $teamA['contingent']['name'] }}" class="w-full h-full object-cover">
                            @else
                                <span>{{ substr($teamA['contingent']['name'] ?? '?', 0, 1) }}</span>
                            @endif
                        </div>

                        <h2 class="text-xl font-bold text-gray-800 text-center">
                            {{ $teamA['contingent']['name'] ?? 'TBD' }}
                        </h2>
                    </div>

                    {{-- Score --}}
                    <div class="flex flex-col items-center justify-center px-4">
                        <div class="text-[10px] font-bold text-gray-400 tracking-[0.2em] mb-2 uppercase">Score</div>

                        <div class="flex items-center gap-6">
                            <span class="text-5xl md:text-6xl font-black {{ $match['status'] === 'scheduled' ? 'text-gray-300' : 'text-gray-900' }}">
                                {{ $match['status'] === 'scheduled' ? '-' : $scoreA }}
                            </span>

                            <span class="text-2xl font-bold text-gray-300">VS</span>

                            <span class="text-5xl md:text-6xl font-black {{ $match['status'] === 'scheduled' ? 'text-gray-300' : 'text-gray-900' }}">
                                {{ $match['status'] === 'scheduled' ? '-' : $scoreB }}
                            </span>
                        </div>
                    </div>

                    {{-- Team B --}}
                    <div class="flex flex-col items-center flex-1 w-full max-w-[200px]">
                        <div class="w-24 h-24 rounded-full bg-white shadow-md border border-gray-100 flex items-center justify-center text-3xl font-black text-gray-400 mb-4 overflow-hidden">
                            @if($teamB['contingent']['image_url'])
                                <img src="{{ $teamB['contingent']['image_url'] }}" alt="{{ $teamB['contingent']['name'] }}" class="w-full h-full object-cover">
                            @else
                                <span>{{ substr($teamB['contingent']['name'] ?? '?', 0, 1) }}</span>
                            @endif
                        </div>

                        <h2 class="text-xl font-bold text-gray-800 text-center">
                            {{ $teamB['contingent']['name'] ?? 'TBD' }}
                        </h2>
                    </div>
                </div>

                {{-- Info Grid --}}
                <div class="grid grid-cols-2 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                    <div class="p-4 flex flex-col items-center justify-center text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M3 11h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
                        </svg>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Tanggal</p>
                        <p class="font-medium text-gray-800 mt-1">{{ $formattedDate }}</p>
                    </div>

                    <div class="p-4 flex flex-col items-center justify-center text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z" />
                        </svg>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Waktu</p>
                        <p class="font-medium text-gray-800 mt-1">{{ $match['match_time'] ?? '-' }}</p>
                    </div>

                    <div class="p-4 flex flex-col items-center justify-center text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z" />
                            <circle cx="12" cy="10" r="2.5" />
                        </svg>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Lokasi</p>
                        <p class="font-medium text-gray-800 mt-1">{{ $match['location'] ?? '-' }}</p>
                    </div>

                    <div class="p-4 flex flex-col items-center justify-center text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM4 21a8 8 0 0 1 16 0" />
                        </svg>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Wasit</p>
                        <p class="font-medium text-gray-800 mt-1">{{ $match['referee_name'] ?? 'TBD' }}</p>
                    </div>
                </div>
            </div>

            {{-- Player Lists --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach([
                    ['team' => $teamA, 'accent' => 'blue'],
                    ['team' => $teamB, 'accent' => 'red'],
                ] as $item)
                    @php
                        $team = $item['team'];
                        $accent = $item['accent'];

                        $bgClass = $accent === 'blue'
                            ? 'bg-blue-50 text-blue-600 border-blue-100'
                            : 'bg-red-50 text-red-600 border-red-100';

                        $barClass = $accent === 'blue'
                            ? 'bg-blue-500'
                            : 'bg-red-500';

                        $teamName = $team['contingent']['name'] ?? 'TBD';
                        $players = $team['players'] ?? [];
                    @endphp

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between gap-4">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2 overflow-hidden">
                                <div class="w-2 h-6 {{ $barClass }} rounded-full shrink-0"></div>
                                <span class="truncate" title="Daftar Pemain {{ $teamName }}">
                                    Daftar Pemain {{ $teamName }}
                                </span>
                            </h3>

                            <span class="text-xs font-bold text-gray-500 bg-gray-200 px-2.5 py-1 rounded-full shrink-0">
                                {{ count($players) }} Pemain
                            </span>
                        </div>

                        <ul class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                            @forelse($players as $player)
                                <li class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full {{ $bgClass }} flex items-center justify-center font-bold text-sm overflow-hidden border">
                                            @if(!empty($player['photo_path']))
                                                <img src="{{ $player['photo_path'] }}" alt="{{ $player['name'] }}" class="w-full h-full object-cover">
                                            @else
                                                {{ substr($player['name'] ?? '?', 0, 1) }}
                                            @endif
                                        </div>

                                        <div>
                                            <p class="font-semibold text-gray-800 flex items-center gap-2">
                                                {{ $player['name'] }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $player['nim_nip'] ?? '-' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-end">
                                        @if(!empty($player['risk_lvl']))
                                            <span class="text-[10px] px-2 py-0.5 rounded-full border font-semibold flex items-center gap-1 {{ $getRiskBadge($player['risk_lvl']) }}">
                                                @if($isLowRisk($player['risk_lvl']))
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5-3v5c0 5-3.5 9-8 10-4.5-1-8-5-8-10V7l8-4 8 4Z" />
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M20 7v5c0 5-3.5 9-8 10-4.5-1-8-5-8-10V7l8-4 8 4Z" />
                                                    </svg>
                                                @endif

                                                Risiko: {{ $player['risk_lvl'] }}
                                            </span>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li class="p-8 text-center text-gray-500 text-sm">
                                    Tidak ada pemain terdaftar.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                @endforeach
            </div>
        </main>
    </div>
</x-layout-public>