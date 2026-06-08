@props(['question'])

<div class="flex flex-wrap gap-4">
    <label class="flex cursor-pointer items-center gap-3 text-sm text-gray-800 bg-gray-50 hover:bg-red-50/50 px-5 py-3 rounded-lg border border-gray-200 transition-all min-w-[120px]"
           :class="{ 'border-[#B41F2A] bg-red-50 font-medium': answers['{{ $question['code'] }}'] === true }">
        <input type="radio" 
               name="{{ $question['code'] }}" 
               x-model="answers['{{ $question['code'] }}']"
               :value="true"
               class="h-4 w-4 accent-[#B41F2A]" />
        Ya
    </label>
    
    <label class="flex cursor-pointer items-center gap-3 text-sm text-gray-800 bg-gray-50 hover:bg-red-50/50 px-5 py-3 rounded-lg border border-gray-200 transition-all min-w-[120px]"
           :class="{ 'border-[#B41F2A] bg-red-50 font-medium': answers['{{ $question['code'] }}'] === false }">
        <input type="radio" 
               name="{{ $question['code'] }}" 
               x-model="answers['{{ $question['code'] }}']"
               :value="false"
               class="h-4 w-4 accent-[#B41F2A]" />
        Tidak
    </label>
</div>
