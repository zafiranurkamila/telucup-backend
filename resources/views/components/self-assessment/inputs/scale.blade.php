@props(['question'])

@php
    $min = $question['min'] ?? 0;
    $max = $question['max'] ?? 10;
@endphp

<div class="mt-3 w-full">
    <div class="w-full overflow-x-auto pb-4 pt-2 -mx-2 px-2 sm:mx-0 sm:px-0" style="scrollbar-width: none; -ms-overflow-style: none;">
        <style>
            .hide-scroll::-webkit-scrollbar { display: none; }
        </style>
        <div class="hide-scroll flex w-full min-w-max items-center rounded-2xl bg-[#f8f9fa] p-1.5 shadow-inner border border-gray-200/75">
            @for ($i = $min; $i <= $max; $i++)
            <label class="relative flex flex-1 h-12 min-w-[3rem] cursor-pointer items-center justify-center rounded-xl text-base sm:text-lg font-bold transition-all duration-300 select-none"
                   :class="answers['{{ $question['code'] }}'] === {{ $i }} 
                        ? 'bg-[#B41F2A] text-white shadow-[0_8px_16px_-6px_rgba(180,31,42,0.5)] scale-110 z-10' 
                        : 'text-gray-500 hover:bg-gray-200/60 hover:text-gray-900 z-0'">
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
