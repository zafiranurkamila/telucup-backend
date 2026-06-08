@props(['recommendation'])

@if(!empty($recommendation))
    <section class="rounded-2xl border border-gray-100 bg-white p-7 shadow-sm">
        <h2 class="mb-4 flex items-center gap-2 text-base font-extrabold text-gray-900">
            <svg class="h-5 w-5 text-[#B41F2A]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
            Rekomendasi
        </h2>
        <div class="rounded-xl border border-gray-100 bg-gray-50 p-5">
            <p class="whitespace-pre-line text-sm leading-6 text-gray-700">
                {{ $recommendation }}
            </p>
        </div>
    </section>
@endif
