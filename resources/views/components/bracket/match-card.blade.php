@props(['role' => 'public', 'matchVar' => 'match'])

<div
    @if($role === 'panitia') 
        @click="openMatchEdit({{ $matchVar }})" 
    @else 
        @click="openMatchDetail({{ $matchVar }})" 
    @endif
    :class="{
        'ring-2 ring-brand': (typeof editingMatchId !== 'undefined' ? editingMatchId : (typeof detailMatch !== 'undefined' && detailMatch ? detailMatch.id : null)) === {{ $matchVar }}.id,
        'opacity-50': {{ $matchVar }}.status === 'bye',
        'cursor-pointer hover:shadow-md hover:-translate-y-0.5': {{ $matchVar }}.status !== 'bye',
    }"
    class="bg-white rounded-xl border border-gray-200 shadow-sm transition-all duration-150 overflow-hidden relative z-10"
>
    {{-- Status bar --}}
    <div class="flex items-center justify-between px-3 py-1.5 border-b border-gray-100 bg-gray-50/50">
        <span class="text-[10px] font-bold text-gray-400 uppercase flex items-center gap-1">
            <svg class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span x-text="'MATCH #' + {{ $matchVar }}.match_number"></span>
        </span>
        <span
            :class="{
                'text-gray-500': {{ $matchVar }}.status === 'scheduled',
                'text-red-600 flex items-center gap-1': {{ $matchVar }}.status === 'live',
                'text-gray-900': {{ $matchVar }}.status === 'finished',
                'text-gray-400': {{ $matchVar }}.status === 'bye',
            }"
            class="text-[10px] font-bold uppercase"
        >
            <template x-if="{{ $matchVar }}.status === 'live'">
                <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-pulse"></span>
            </template>
            <span x-text="{{ $matchVar }}.status === 'finished' ? 'SELESAI' : {{ $matchVar }}.status"></span>
        </span>
    </div>

    {{-- Team A --}}
    <div
        :class="{
            'bg-brand/5': {{ $matchVar }}.winner && {{ $matchVar }}.winner.registration_id === {{ $matchVar }}.team_a?.registration_id,
        }"
        class="px-3 py-2.5 flex items-center justify-between"
        @if($role === 'panitia')
        :draggable="['scheduled', 'bye'].includes({{ $matchVar }}.status)"
        @dragstart="dragStartMatch($event, {{ $matchVar }}, 'a')"
        @dragover.prevent="if(['scheduled', 'bye'].includes({{ $matchVar }}.status)) { $event.dataTransfer.dropEffect = 'move'; $event.target.closest('.px-3').classList.add('ring-2', 'ring-brand', 'ring-inset'); }"
        @dragleave="$event.target.closest('.px-3')?.classList.remove('ring-2', 'ring-brand', 'ring-inset')"
        @dragend="$event.target.classList.remove('opacity-50')"
        @drop="dropMatch($event, {{ $matchVar }}, 'a')"
        @endif
    >
        <div class="flex items-center gap-2 overflow-hidden pointer-events-none">
            <img :src="{{ $matchVar }}.team_a?.contingent?.logo_url || 'https://ui-avatars.com/api/?name=' + ({{ $matchVar }}.team_a?.contingent?.name || 'A') + '&background=f3f4f6&color=9ca3af'" class="w-6 h-6 rounded-full object-cover shrink-0">
            <span class="text-sm font-bold text-gray-800 truncate" x-text="{{ $matchVar }}.team_a?.contingent?.name ?? 'TBD'"></span>
        </div>
        <span :class="{'text-brand': {{ $matchVar }}.winner && {{ $matchVar }}.winner.registration_id === {{ $matchVar }}.team_a?.registration_id, 'text-gray-900': !{{ $matchVar }}.winner}" class="text-sm font-black ml-2 tabular-nums" x-text="{{ $matchVar }}.score_a ?? ''"></span>
    </div>

    <div class="border-t border-gray-100 mx-3"></div>

    {{-- Team B --}}
    <div
        :class="{
            'bg-brand/5': {{ $matchVar }}.winner && {{ $matchVar }}.winner.registration_id === {{ $matchVar }}.team_b?.registration_id,
        }"
        class="px-3 py-2.5 flex items-center justify-between"
        @if($role === 'panitia')
        :draggable="['scheduled', 'bye'].includes({{ $matchVar }}.status)"
        @dragstart="dragStartMatch($event, {{ $matchVar }}, 'b')"
        @dragover.prevent="if(['scheduled', 'bye'].includes({{ $matchVar }}.status)) { $event.dataTransfer.dropEffect = 'move'; $event.target.closest('.px-3').classList.add('ring-2', 'ring-brand', 'ring-inset'); }"
        @dragleave="$event.target.closest('.px-3')?.classList.remove('ring-2', 'ring-brand', 'ring-inset')"
        @dragend="$event.target.classList.remove('opacity-50')"
        @drop="dropMatch($event, {{ $matchVar }}, 'b')"
        @endif
    >
        <div class="flex items-center gap-2 overflow-hidden pointer-events-none">
            <img :src="{{ $matchVar }}.team_b?.contingent?.logo_url || 'https://ui-avatars.com/api/?name=' + ({{ $matchVar }}.team_b?.contingent?.name || 'B') + '&background=f3f4f6&color=9ca3af'" class="w-6 h-6 rounded-full object-cover shrink-0">
            <span class="text-sm font-bold text-gray-800 truncate" x-text="{{ $matchVar }}.team_b?.contingent?.name ?? 'TBD'"></span>
        </div>
        <span :class="{'text-brand': {{ $matchVar }}.winner && {{ $matchVar }}.winner.registration_id === {{ $matchVar }}.team_b?.registration_id, 'text-gray-900': !{{ $matchVar }}.winner}" class="text-sm font-black ml-2 tabular-nums" x-text="{{ $matchVar }}.score_b ?? ''"></span>
    </div>

    {{-- Panitia Check-in & Start Match Action --}}
    @if($role === 'panitia')
    <template x-if="{{ $matchVar }}.status === 'scheduled' && {{ $matchVar }}.team_a?.registration_id && {{ $matchVar }}.team_b?.registration_id">
        <div class="px-3 py-2 border-t border-gray-100 bg-white">
            <template x-if="{{ $matchVar }}.checked_in_count >= {{ $matchVar }}.total_players && {{ $matchVar }}.total_players > 0">
                <button
                    type="button"
                    @click.stop="openMatchEdit({{ $matchVar }})"
                    class="w-full flex items-center justify-center gap-1.5 bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white text-[11px] font-bold py-1.5 px-3 rounded-lg transition-all duration-150 shadow-sm"
                >
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z" />
                    </svg>
                    Mulai Pertandingan
                </button>
            </template>
            <template x-if="{{ $matchVar }}.checked_in_count < {{ $matchVar }}.total_players || {{ $matchVar }}.total_players === 0">
                <a
                    :href="'/dashboard/panitia/verifikasi?match_id=' + {{ $matchVar }}.id + '&sport_id=' + (selectedSportId || '') + '&category_id=' + (selectedCategoryId || '')"
                    @click.stop
                    class="w-full flex items-center justify-center gap-1.5 bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white text-[11px] font-bold py-1.5 px-3 rounded-lg transition-all duration-150 shadow-sm"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="'Check-in Pemain (' + ({{ $matchVar }}.checked_in_count || 0) + '/' + ({{ $matchVar }}.total_players || 0) + ')'"></span>
                </a>
            </template>
        </div>
    </template>
    @endif

    {{-- Schedule info --}}
    <div class="px-3 py-2 border-t border-gray-100 bg-white flex items-center justify-between">
        <div class="flex items-center text-[10px] text-gray-500 font-medium">
            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span x-text="{{ $matchVar }}.match_date ? (new Date({{ $matchVar }}.match_date).toLocaleDateString('id-ID', {day:'numeric', month:'short'})) : 'TBD'"></span>
            <template x-if="{{ $matchVar }}.match_time"><span class="ml-1" x-text="{{ $matchVar }}.match_time"></span></template>
        </div>
        <div class="text-[9px] font-bold text-gray-800 hover:text-brand cursor-pointer transition-colors uppercase">
            Detail Pertandingan &gt;
        </div>
    </div>
</div>
