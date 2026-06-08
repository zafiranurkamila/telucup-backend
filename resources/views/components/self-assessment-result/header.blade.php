@props(['playerName', 'contingent', 'sportBranch', 'createdAt', 'riskConfig'])

<div class="mb-8 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
    <div>
        <div class="mb-3 flex flex-wrap items-center gap-2 text-xs font-semibold">
            <span class="rounded-full border px-3 py-1 {{ $riskConfig['bg'] }} {{ $riskConfig['text'] }} {{ $riskConfig['border'] }}">
                Dianalisis oleh Algoritma • Hasil Instan
            </span>
            <span class="flex items-center gap-1 text-gray-400">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                {{ \Carbon\Carbon::parse($createdAt)->translatedFormat('d F Y H:i') }}
            </span>
        </div>
        <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 lg:text-3xl">
            Hasil Self-Assessment — <span class="text-[#B41F2A]">{{ $playerName ?? 'Peserta' }}</span>
        </h1>
        @if($contingent || $sportBranch)
            <p class="mt-1 text-sm text-gray-500">
                {{ $contingent }}
                @if($contingent && $sportBranch) • @endif
                {{ $sportBranch }}
            </p>
        @endif
    </div>

    <a href="{{ request()->user()->role === 'player' ? route('dashboard.player.self-assessment.index') : route('dashboard.pic.self-assessment.index') }}"
       class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-50">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg> 
        Isi Ulang Assessment
    </a>
</div>
