@props(['question'])

<div class="grid grid-cols-2 gap-3">
    <label class="flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-gray-700 shadow-sm transition-all hover:border-[#B41F2A]/40 hover:bg-red-50/40"
           :class="{ 'border-[#B41F2A] bg-red-50 text-[#B41F2A] ring-1 ring-[#B41F2A]/20': answers['{{ $question['code'] }}'] === 1 }">
        <input type="radio"
               name="{{ $question['code'] }}"
               x-model.number="answers['{{ $question['code'] }}']"
               value="1"
               class="h-4 w-4 accent-[#B41F2A]" />
        Ya
    </label>

    <label class="flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-gray-700 shadow-sm transition-all hover:border-[#B41F2A]/40 hover:bg-red-50/40"
           :class="{ 'border-[#B41F2A] bg-red-50 text-[#B41F2A] ring-1 ring-[#B41F2A]/20': answers['{{ $question['code'] }}'] === 0 }">
        <input type="radio"
               name="{{ $question['code'] }}"
               x-model.number="answers['{{ $question['code'] }}']"
               value="0"
               class="h-4 w-4 accent-[#B41F2A]" />
        Tidak
    </label>
</div>
