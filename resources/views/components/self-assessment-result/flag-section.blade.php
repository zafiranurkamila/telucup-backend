@props(['redFlags', 'yellowFlags'])

@if(count($redFlags) > 0)
    <section class="rounded-2xl border border-red-200 bg-red-50 p-7 shadow-sm">
        <h2 class="mb-4 flex items-center gap-2 text-base font-extrabold text-[#B41F2A]">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Red Flag Terdeteksi ({{ count($redFlags) }})
        </h2>
        <div class="space-y-3">
            @foreach($redFlags as $flag)
                <div class="rounded-lg border border-red-100 bg-white p-4">
                    <p class="text-sm font-bold text-gray-800">{{ $flag['text'] }}</p>
                    @if(!empty($flag['reason']))
                        <p class="mt-1 text-xs leading-relaxed text-[#B41F2A]">{{ $flag['reason'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endif

@if(count($yellowFlags) > 0)
    <section class="rounded-2xl border border-amber-200 bg-amber-50 p-7 shadow-sm">
        <h2 class="mb-4 flex items-center gap-2 text-base font-extrabold text-amber-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            Perhatian — {{ count($yellowFlags) }} Yellow Flag
        </h2>
        <div class="space-y-3">
            @foreach($yellowFlags as $flag)
                <div class="rounded-lg border border-amber-100 bg-white p-4">
                    <p class="text-sm font-bold text-gray-800">{{ $flag['text'] }}</p>
                    @if(!empty($flag['reason']))
                        <p class="mt-1 text-xs leading-relaxed text-amber-700">{{ $flag['reason'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endif

@if(count($redFlags) === 0 && count($yellowFlags) === 0)
    <section class="rounded-2xl border border-green-100 bg-green-50 p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <svg class="shrink-0 h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <p class="text-sm font-bold text-green-800">
                    Tidak Ada Red Flag atau Yellow Flag
                </p>
                <p class="mt-0.5 text-xs text-green-700">
                    Tidak ditemukan indikator risiko signifikan pada screening ini.
                </p>
            </div>
        </div>
    </section>
@endif
