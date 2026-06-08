@props(['question'])

<div class="space-y-2.5">
    @foreach($question['options'] as $opt)
    @php
        $val = is_array($opt) ? $opt['value'] : $opt;
        $label = is_array($opt) ? $opt['label'] : $opt;
    @endphp
    <label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:border-[#B41F2A]/40 hover:bg-red-50/40"
           :class="{ 'border-[#B41F2A] bg-red-50 text-[#B41F2A] ring-1 ring-[#B41F2A]/20': answers['{{ $question['code'] }}'] === '{{ $val }}' }">
        <input type="radio"
               name="{{ $question['code'] }}"
               x-model="answers['{{ $question['code'] }}']"
               value="{{ $val }}"
               class="h-4 w-4 accent-[#B41F2A]" />
        <span class="leading-relaxed">{{ $label }}</span>
    </label>
    @endforeach
</div>
