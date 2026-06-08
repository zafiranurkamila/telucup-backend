@props(['playerName', 'contingent', 'sportBranch', 'snapshot', 'assessmentId'])

<section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-center gap-4">
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-400">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
        </div>
        <div class="min-w-0">
            <h2 class="truncate text-lg font-extrabold text-gray-900 leading-tight">
                {{ $playerName ?? '—' }}
            </h2>
            <p class="text-sm text-gray-500 truncate">
                {{ $contingent ?? 'Kontingen tidak diketahui' }}
            </p>
            <p class="mt-0.5 text-xs font-bold text-[#B41F2A]">
                {{ $sportBranch ?? 'Cabang olahraga tidak diketahui' }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 rounded-xl border border-gray-100 bg-gray-50 p-4">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-wide text-gray-400">Usia</p>
            <p class="mt-1 text-sm font-bold text-gray-800">{{ $snapshot['age'] ?? '—' }} tahun</p>
        </div>
        <div class="text-right">
            <p class="text-[10px] font-extrabold uppercase tracking-wide text-gray-400">BMI</p>
            <p class="mt-1 text-sm font-bold text-gray-800">
                @if(isset($snapshot['bmi']))
                    {{ number_format($snapshot['bmi'], 1) }}
                    @php
                        $bmi = $snapshot['bmi'];
                        $cat = $bmi < 18.5 ? 'Underweight' : ($bmi < 25 ? 'Normal' : ($bmi < 30 ? 'Overweight' : 'Obese'));
                    @endphp
                    ({{ $cat }})
                @else
                    —
                @endif
            </p>
        </div>
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-wide text-gray-400">Kacamata</p>
            <p class="mt-1 text-sm font-bold text-gray-800">{{ !empty($snapshot['is_kacamata']) ? 'Ya' : 'Tidak' }}</p>
        </div>
        <div class="text-right">
            <p class="text-[10px] font-extrabold uppercase tracking-wide text-gray-400">ID Assessment</p>
            <p class="mt-1 text-sm font-bold text-gray-800">#{{ str_pad($assessmentId, 5, '0', STR_PAD_LEFT) }}</p>
        </div>
    </div>
</section>
