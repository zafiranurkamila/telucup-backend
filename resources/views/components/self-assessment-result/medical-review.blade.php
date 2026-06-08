@props(['medicalReview'])

<section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <h2 class="mb-4 text-xs font-extrabold uppercase tracking-wide text-gray-500">
        Peninjauan Medis
    </h2>

    @if(!empty($medicalReview['reviewed_at']))
        <div class="space-y-3">
            <div class="flex items-center gap-2 rounded-lg px-4 py-3 {{ $medicalReview['is_allowed_to_play'] ? 'border border-green-100 bg-green-50' : 'border border-red-100 bg-red-50' }}">
                @if($medicalReview['is_allowed_to_play'])
                    <svg class="shrink-0 h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                @else
                    <svg class="shrink-0 h-4 w-4 text-[#B41F2A]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                @endif
                <p class="text-sm font-bold {{ $medicalReview['is_allowed_to_play'] ? 'text-green-700' : 'text-[#B41F2A]' }}">
                    {{ $medicalReview['is_allowed_to_play'] ? 'Diizinkan Bermain' : 'Tidak Diizinkan / Perlu Istirahat' }}
                </p>
            </div>

            @if(!empty($medicalReview['medical_notes']))
                <div>
                    <p class="mb-1 text-[10px] font-extrabold uppercase tracking-wide text-gray-400">
                        Catatan Medis
                    </p>
                    <p class="rounded-lg border border-gray-100 bg-gray-50 p-3 text-sm leading-relaxed text-gray-700">
                        {{ $medicalReview['medical_notes'] }}
                    </p>
                </div>
            @endif

            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Direview: {{ \Carbon\Carbon::parse($medicalReview['reviewed_at'])->translatedFormat('d F Y H:i') }}
            </div>

            @if(!empty($medicalReview['pic_confirmed']))
                <div class="flex items-center gap-1.5 text-xs text-green-600">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Dikonfirmasi oleh PIC Kontingen
                </div>
            @endif
        </div>
    @else
        <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-600">
            Review medis belum dilakukan oleh panitia medis.
        </div>
    @endif
</section>
