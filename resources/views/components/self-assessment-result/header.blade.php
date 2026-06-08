@props(['playerName', 'contingent', 'sportBranch', 'createdAt', 'riskConfig'])

<div class="mb-8 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
    <div>
        <div class="mb-3 flex flex-wrap items-center gap-2 text-xs font-semibold">
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


</div>
