@props(['title', 'subtitle' => null, 'code' => null, 'index' => null, 'total' => null])

<section class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-all hover:border-gray-300">
    <div class="border-b border-gray-100 bg-white px-5 py-5 sm:px-6">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#B41F2A] text-sm font-black text-white shadow-sm shadow-red-200">
                {{ $code ?: $index }}
            </div>
            <div class="min-w-0">
                @if($index && $total)
                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-gray-500">Bagian {{ $index }} / {{ $total }}</span>
                @endif
                <h2 class="mt-2 text-lg font-black leading-tight text-gray-950">{{ $title }}</h2>
                @if($subtitle)
                    <p class="mt-1 text-sm leading-relaxed text-gray-500">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="divide-y divide-gray-100">
        {{ $slot }}
    </div>
</section>
