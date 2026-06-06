<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @forelse($todayMatches as $match)
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-center mb-3 border-b border-gray-100 pb-2">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $match->sport->name ?? 'Cabang Olahraga' }}</span>
                <span class="text-xs font-medium bg-gray-100 text-gray-600 px-2 py-1 rounded-md">{{ $match->round_name ?? 'Round' }}</span>
            </div>
            
            <div class="flex justify-between items-center py-2">
                {{-- Team A --}}
                <div class="flex flex-col items-center w-1/3">
                    <div class="w-12 h-12 rounded-full bg-gray-50 border border-gray-200 mb-2 overflow-hidden flex items-center justify-center">
                        @if($match->registrationA && $match->registrationA->contingent && $match->registrationA->contingent->image_url)
                            <img src="{{ $match->registrationA->contingent->image_url }}" alt="Logo" class="w-full h-full object-cover" onerror="this.style.display='none'">
                        @else
                            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        @endif
                    </div>
                    <span class="text-[11px] font-bold text-center leading-tight truncate w-full" title="{{ $match->registrationA->contingent->name ?? 'TBD' }}">
                        {{ Str::limit($match->registrationA->contingent->name ?? 'TBD', 15) }}
                    </span>
                </div>
                
                {{-- Score & Status --}}
                <div class="flex flex-col items-center justify-center px-2 w-1/3">
                    @if(in_array($match->status, ['completed', 'live']))
                        <div class="flex items-center gap-2 text-2xl font-black text-gray-800">
                            <span>{{ $match->score_a ?? 0 }}</span>
                            <span class="text-gray-300">-</span>
                            <span>{{ $match->score_b ?? 0 }}</span>
                        </div>
                        @if($match->status === 'live')
                            <span class="mt-1 text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full animate-pulse border border-red-200">LIVE</span>
                        @endif
                    @else
                        <div class="text-sm font-bold text-gray-800">{{ \Carbon\Carbon::parse($match->match_time)->format('H:i') }}</div>
                        <div class="text-[10px] text-gray-500 mt-1 uppercase font-medium">{{ $match->status ?? 'Scheduled' }}</div>
                    @endif
                </div>

                {{-- Team B --}}
                <div class="flex flex-col items-center w-1/3">
                    <div class="w-12 h-12 rounded-full bg-gray-50 border border-gray-200 mb-2 overflow-hidden flex items-center justify-center">
                        @if($match->registrationB && $match->registrationB->contingent && $match->registrationB->contingent->image_url)
                            <img src="{{ $match->registrationB->contingent->image_url }}" alt="Logo" class="w-full h-full object-cover" onerror="this.style.display='none'">
                        @else
                            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        @endif
                    </div>
                    <span class="text-[11px] font-bold text-center leading-tight truncate w-full" title="{{ $match->registrationB->contingent->name ?? 'TBD' }}">
                        {{ Str::limit($match->registrationB->contingent->name ?? 'TBD', 15) }}
                    </span>
                </div>
            </div>
            
            <div class="mt-3 pt-3 border-t border-gray-50 flex justify-between items-center">
                <div class="flex items-center text-[10px] text-gray-500">
                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ Str::limit($match->location ?? 'Belum ditentukan', 20) }}
                </div>
                <a href="{{ url('/dashboard/pic-kontingen/jadwal') }}" class="text-[10px] font-bold text-brand hover:underline">Detail &rarr;</a>
            </div>
        </div>
    @empty
        <div class="col-span-1 md:col-span-2 text-center text-gray-500 py-10 bg-white border border-gray-200 rounded-xl shadow-sm">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <p>Tidak ada pertandingan hari ini.</p>
        </div>
    @endforelse
</div>
