@props(['title', 'subtitle' => null, 'code' => null])

<section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
    <div class="px-6 pb-2 pt-6 sm:px-8 sm:pt-8">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-50 text-[#B41F2A]">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s-7-4.5-7-10a4 4 0 0 1 7-2.65A4 4 0 0 1 19 11c0 5.5-7 10-7 10Z" />
                </svg>
            </div>
            <div class="min-w-0">
                <h2 class="text-xl font-black leading-tight text-gray-950">{{ $title }}</h2>
                @if($subtitle)
                    <p class="mt-1 text-sm leading-relaxed text-gray-500">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="px-6 py-4 sm:px-8 sm:pb-8">
        {{ $slot }}
    </div>
</section>
