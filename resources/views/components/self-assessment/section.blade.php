@props(['title', 'subtitle' => null])

<section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm mb-6 transition-all hover:shadow-md">
    <div class="mb-5 flex items-start gap-3">
        <div class="mt-1 flex h-7 w-7 items-center justify-center rounded-md bg-red-50 text-sm font-bold text-[#B41F2A]">
            ◉
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-900">{{ $title }}</h2>
            @if($subtitle)
            <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    <div class="space-y-6">
        {{ $slot }}
    </div>
</section>
