@props(['question'])

@php
    $min = $question['min'] ?? 0;
    $max = $question['max'] ?? 10;
@endphp

<div class="w-full">
    <div class="w-full overflow-x-auto pb-2" style="scrollbar-width: none; -ms-overflow-style: none;">
        <style>
            .hide-scroll::-webkit-scrollbar { display: none; }
        </style>
        <div class="hide-scroll flex w-full min-w-[32rem] items-center rounded-md border border-gray-200 bg-gray-50 p-1.5 sm:min-w-0">
            @for ($i = $min; $i <= $max; $i++)
            <label class="relative flex h-10 flex-1 cursor-pointer select-none items-center justify-center rounded text-sm font-black transition-all"
                   :class="answers['{{ $question['code'] }}'] === {{ $i }}
                        ? 'bg-[#B41F2A] text-white shadow-sm'
                        : 'text-gray-500 hover:bg-gray-200 hover:text-gray-900'">
                <input type="radio"
                       name="{{ $question['code'] }}"
                       x-model.number="answers['{{ $question['code'] }}']"
                       :value="{{ $i }}"
                       class="hidden" />
                {{ $i }}
            </label>
            @endfor
        </div>
    </div>
</div>
