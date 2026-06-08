@props(['question', 'number' => null])

@php
    $required = $question['required'] ?? true;
@endphp

<div id="question-{{ $question['code'] }}"
     class="border-b border-gray-200 py-7 first:pt-4 last:border-b-0 last:pb-0 transition-all duration-300"
     :class="{ 'rounded-lg border border-red-200 bg-red-50/40 px-4 shadow-sm': unansweredCodes.includes('{{ $question['code'] }}') }">

    <div class="mb-5 flex items-start justify-between gap-5">
        <p class="text-base font-bold leading-relaxed text-gray-900">
            {{ $question['text'] }}
            @if($required)
                <span class="ml-1 text-[#B41F2A]">*</span>
            @endif
        </p>

        @if($required)
            <span class="mt-1 shrink-0 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-wide transition-colors"
                  :class="unansweredCodes.includes('{{ $question['code'] }}') ? 'border-[#B41F2A] bg-[#B41F2A] text-white' : 'border-red-200 bg-white text-[#B41F2A]'">
                <span x-text="unansweredCodes.includes('{{ $question['code'] }}') ? 'Wajib diisi' : 'Wajib diisi'"></span>
            </span>
        @endif
    </div>

    <div>
        @if($question['type'] === 'boolean')
            <x-self-assessment.inputs.boolean :question="$question" />
        @elseif($question['type'] === 'single_choice' || $question['type'] === 'select')
            <x-self-assessment.inputs.single-choice :question="$question" />
        @elseif($question['type'] === 'multi_choice')
            <x-self-assessment.inputs.multi-choice :question="$question" />
        @elseif($question['type'] === 'number' || $question['type'] === 'integer')
            <x-self-assessment.inputs.number :question="$question" />
        @elseif($question['type'] === 'open_text' || $question['type'] === 'text')
            <x-self-assessment.inputs.open-text :question="$question" />
        @elseif($question['type'] === 'scale')
            <x-self-assessment.inputs.scale :question="$question" />
        @endif
    </div>
</div>
