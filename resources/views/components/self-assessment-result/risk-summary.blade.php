@props(['riskConfig', 'totalScore', 'requiresClearance'])

<section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="h-1.5 {{ $riskConfig['headerBar'] }}"></div>
    <div class="p-7">
        <div class="flex flex-col gap-6 md:flex-row md:items-center">
            <div class="flex-1">
                <div class="mb-3 flex flex-wrap items-center gap-3">
                    <span class="rounded-full px-6 py-2 text-sm font-extrabold {{ $riskConfig['badge'] }}">
                        {{ $riskConfig['label'] }}
                    </span>
                    <span class="text-sm font-bold {{ $riskConfig['text'] }}">
                        Skor Total: {{ number_format($totalScore, 1) }} / 100
                    </span>
                </div>
                <p class="max-w-lg text-base font-bold leading-snug text-gray-800">
                    {{ $riskConfig['description'] }}
                </p>

                @if($requiresClearance)
                    <div class="mt-4 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5">
                        <svg class="shrink-0 h-4 w-4 text-[#B41F2A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-xs font-bold text-[#B41F2A]">
                            Memerlukan clearance medis sebelum diizinkan bertanding
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section
