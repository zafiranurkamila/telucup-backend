@props(['question'])

<div class="grid gap-2.5">
    @foreach($question['options'] as $opt)
    @php
        $val = is_array($opt) ? $opt['value'] : $opt;
        $label = is_array($opt) ? $opt['label'] : $opt;
    @endphp
    <label class="flex cursor-pointer items-center gap-3 rounded-md border border-transparent bg-gray-50 px-4 py-3 text-sm font-medium text-gray-800 transition-all hover:bg-red-50/50"
           :class="{ 'border-red-100 bg-red-50 text-[#B41F2A]': answers['{{ $question['code'] }}'] === '{{ $val }}' }">
        <input type="radio"
               name="{{ $question['code'] }}"
               x-model="answers['{{ $question['code'] }}']"
               value="{{ $val }}"
               class="h-4 w-4 border-gray-300 accent-[#B41F2A] focus:ring-[#B41F2A]" />
        {{ $label }}
    </label>
    @endforeach
</div>
