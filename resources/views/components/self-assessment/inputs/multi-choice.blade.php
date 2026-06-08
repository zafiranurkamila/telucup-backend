@props(['question'])

<div class="grid gap-2.5">
    @foreach($question['options'] as $opt)
    @php
        $val = is_array($opt) ? $opt['value'] : $opt;
        $label = is_array($opt) ? $opt['label'] : $opt;
    @endphp
    <label class="flex cursor-pointer items-center gap-3 rounded-md border border-transparent bg-gray-50 px-4 py-3 text-sm font-medium text-gray-800 transition-all hover:bg-red-50/50"
           :class="{ 'border-red-100 bg-red-50 text-[#B41F2A]': answers['{{ $question['code'] }}'] && answers['{{ $question['code'] }}'].includes('{{ $val }}') }">
        <input type="checkbox"
               name="{{ $question['code'] }}[]"
               x-model="answers['{{ $question['code'] }}']"
               value="{{ $val }}"
               @change="if($el.checked && '{{ $val }}' === 'none') { answers['{{ $question['code'] }}'] = ['none']; } else if($el.checked) { answers['{{ $question['code'] }}'] = answers['{{ $question['code'] }}'].filter(val => val !== 'none'); }"
               class="h-5 w-5 rounded border-gray-300 accent-[#B41F2A] focus:ring-[#B41F2A]" />
        {{ $label }}
    </label>
    @endforeach
</div>
