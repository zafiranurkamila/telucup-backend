@props(['match'])

@php
    // Helper untuk memformat tanggal
    $formatDate = function($dateString) {
        if (!$dateString) return "TBD";
        try {
            return \Carbon\Carbon::parse($dateString)->locale('id')->isoFormat('D MMM YYYY');
        } catch (\Exception $e) {
            return "TBD";
        }
    };

    // Helper untuk badge status
    $status = strtolower($match->status ?? '');
@endphp

<a href="#" class="block group h-full">
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl hover:border-blue-200 hover:-translate-y-1 transition-all duration-300 relative cursor-pointer flex flex-col h-full">
        
        {{-- Top Header --}}
        <div class="p-4 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between gap-2">
            <div class="flex flex-col">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ $match->sport?->name ?? 'Cabang Olahraga' }}</span>
                <span class="text-sm font-semibold text-gray-800">{{ $match->round_name ?? 'Round' }}</span>
            </div>
            <div>
                @if($status === 'scheduled')
                    <span class="bg-gray-100 text-gray-600 border border-gray-200 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">Scheduled</span>
                @elseif($status === 'live')
                    <span class="bg-red-100 text-red-600 border border-red-200 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider flex items-center gap-1.5 animate-pulse">
                        <span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span>
                        Live Now
                    </span>
                @elseif($status === 'finished')
                    <span class="bg-emerald-100 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">Finished</span>
                @else
                    <span class="bg-gray-100 text-gray-600 border border-gray-200 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">{{ strtoupper($status ?: 'TBD') }}</span>
                @endif
            </div>
        </div>

        {{-- Main Content (Teams & Score) --}}
        <div class="p-6 flex-1 flex flex-col justify-center relative">
            {{-- Subtle VS watermark background --}}
            <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none">
                <span class="text-8xl font-black italic">VS</span>
            </div>

            <div class="flex items-center justify-between relative z-10 gap-2">
                
                {{-- Team A --}}
                @php
                    $teamA = $match->registrationA?->contingent ?? null;
                    $nameA = $teamA->name ?? 'TBD';
                    $logoA = $teamA->image_url ?? null;
                @endphp
                <div class="flex flex-col items-center flex-1 w-0">
                    <div class="w-14 h-14 md:w-16 md:h-16 rounded-full bg-white shadow-sm border border-gray-100 flex items-center justify-center overflow-hidden mb-3 group-hover:scale-105 transition-transform">
                        @if($logoA)
                            <img src="{{ $logoA }}" alt="{{ $nameA }}" class="w-full h-full object-cover" />
                        @else
                            <span class="text-gray-400 font-bold text-lg">{{ substr($nameA, 0, 1) }}</span>
                        @endif
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 text-center line-clamp-2" title="{{ $nameA }}">
                        {{ $nameA }}
                    </h3>
                </div>

                {{-- Score / VS Divider --}}
                <div class="flex flex-col items-center justify-center px-4 shrink-0">
                    @if($status === 'scheduled')
                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-xs font-black text-gray-400 italic">
                            VS
                        </div>
                    @else
                        <div class="flex items-center gap-3">
                            <span class="text-3xl font-black text-gray-900">{{ $match->score_a ?? '-' }}</span>
                            <span class="text-gray-300 font-bold">:</span>
                            <span class="text-3xl font-black text-gray-900">{{ $match->score_b ?? '-' }}</span>
                        </div>
                    @endif
                </div>

                {{-- Team B --}}
                @php
                    $teamB = $match->registrationB?->contingent ?? null;
                    $nameB = $teamB->name ?? 'TBD';
                    $logoB = $teamB->image_url ?? null;
                @endphp
                <div class="flex flex-col items-center flex-1 w-0">
                    <div class="w-14 h-14 md:w-16 md:h-16 rounded-full bg-white shadow-sm border border-gray-100 flex items-center justify-center overflow-hidden mb-3 group-hover:scale-105 transition-transform">
                        @if($logoB)
                            <img src="{{ $logoB }}" alt="{{ $nameB }}" class="w-full h-full object-cover" />
                        @else
                            <span class="text-gray-400 font-bold text-lg">{{ substr($nameB, 0, 1) }}</span>
                        @endif
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 text-center line-clamp-2" title="{{ $nameB }}">
                        {{ $nameB }}
                    </h3>
                </div>
                
            </div>
        </div>

        {{-- Bottom Footer Details --}}
        <div class="p-4 bg-white border-t border-gray-100 grid grid-cols-2 gap-y-2 gap-x-4">
            <div class="flex items-center gap-2 text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="text-blue-500 shrink-0 w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span class="text-xs font-medium truncate">{{ $formatDate($match->match_date) }}</span>
            </div>
            <div class="flex items-center gap-2 text-gray-500 justify-end">
                <svg xmlns="http://www.w3.org/2000/svg" class="text-amber-500 shrink-0 w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span class="text-xs font-medium truncate">{{ $match->match_time ? \Carbon\Carbon::parse($match->match_time)->format('H:i') : 'TBD' }}</span>
            </div>
            @if($match->location)
                <div class="flex items-center gap-2 text-gray-500 col-span-2 mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="text-emerald-500 shrink-0 w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span class="text-xs font-medium truncate">{{ $match->location }}</span>
                </div>
            @endif
        </div>
    </div>
</a>
