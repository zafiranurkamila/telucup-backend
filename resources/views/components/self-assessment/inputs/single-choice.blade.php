@props(['question'])

<div class="space-y-3">
    @foreach($question['options'] as $opt)
    @php
        $val = is_array($opt) ? $opt['value'] : $opt;
        $label = is_array($opt) ? $opt['label'] : $opt;
    @endphp
    <label class="flex cursor-pointer items-center gap-3 text-sm text-gray-800 bg-gray-50 hover:bg-red-50/50 px-5 py-3 rounded-lg border border-gray-200 transition-all"
           :class="{ 'border-[#B41F2A] bg-red-50 font-medium': answers['{{ $question['code'] }}'] === '{{ $val }}' }">
        <input type="radio" 
               name="{{ $question['code'] }}" 
               x-model="answers['{{ $question['code'] }}']"
               value="{{ $val }}"
               class="h-4 w-4 accent-[#B41F2A]" />
        {{ $label }}
    </label>
    @endforeach
</div>
