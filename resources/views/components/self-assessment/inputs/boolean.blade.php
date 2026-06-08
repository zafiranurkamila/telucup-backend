@props(['question'])

<div class="flex flex-wrap gap-7">
    <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-800 transition-colors hover:text-[#B41F2A]"
           :class="{ 'text-[#B41F2A]': answers['{{ $question['code'] }}'] === 1 }">
        <input type="radio"
               name="{{ $question['code'] }}"
               x-model.number="answers['{{ $question['code'] }}']"
               value="1"
               class="h-4 w-4 border-gray-300 accent-[#B41F2A] focus:ring-[#B41F2A]" />
        Ya
    </label>

    <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-800 transition-colors hover:text-[#B41F2A]"
           :class="{ 'text-[#B41F2A]': answers['{{ $question['code'] }}'] === 0 }">
        <input type="radio"
               name="{{ $question['code'] }}"
               x-model.number="answers['{{ $question['code'] }}']"
               value="0"
               class="h-4 w-4 border-gray-300 accent-[#B41F2A] focus:ring-[#B41F2A]" />
        Tidak
    </label>
</div>
