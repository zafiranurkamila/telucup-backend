@props(['domainScores'])

@php
    $domains = [
        ['key' => 'cardiovascular', 'label' => 'Kardiovaskular', 'weight' => '35%'],
        ['key' => 'musculoskeletal', 'label' => 'Muskuloskeletal', 'weight' => '30%'],
        ['key' => 'acute_readiness', 'label' => 'Kesiapan Akut', 'weight' => '20%'],
        ['key' => 'psychosocial', 'label' => 'Psikososial', 'weight' => '15%'],
    ];
@endphp

<section class="rounded-2xl border border-gray-100 bg-white p-7 shadow-sm">
    <h3 class="mb-5 flex items-center gap-2 text-sm font-extrabold uppercase tracking-widest text-gray-400">
        Rincian Skor Domain
    </h3>
    <div class="grid gap-6 md:grid-cols-2">
        @foreach($domains as $domain)
            @php $score = $domainScores[$domain['key']] ?? 0; @endphp
            <div>
                <div class="mb-2 flex items-center justify-between">
                    <span class="flex items-center gap-2 text-sm font-bold text-gray-700">
                        {{ $domain['label'] }}
                    </span>
                    <span class="text-xs font-bold text-gray-500">{{ number_format($score, 1) }} / 100</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full rounded-full transition-all duration-1000 
                        {{ $score >= 80 ? 'bg-green-500' : ($score >= 50 ? 'bg-amber-500' : 'bg-[#B41F2A]') }}"
                         style="width: {{ $score }}%;"></div>
                </div>
                <p class="mt-1.5 text-right text-[10px] font-semibold tracking-wider text-gray-400 uppercase">
                    Bobot: {{ $domain['weight'] }}
                </p>
            </div>
        @endforeach
    </div>
</section>
