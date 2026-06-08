@props(['question', 'number' => null])

@php
    $required = $question['required'] ?? true;
    $hasError = false; // We'll handle visual error state via Alpine class binding
@endphp

<div id="question-{{ $question['code'] }}" 
     class="border-b border-gray-100 pb-6 last:border-b-0 last:pb-0 transition-all duration-300"
     :class="{ 'bg-red-50/40 p-4 rounded-xl border border-red-200 shadow-sm': unansweredCodes.includes('{{ $question['code'] }}') }">
    
    <div class="mb-4 flex items-start justify-between gap-4">
        <p class="text-sm font-semibold text-gray-900 leading-relaxed">
            @if($number)
                <span class="mr-2 text-gray-500">{{ $number }}.</span>
            @endif
            {{ $question['text'] }}
            @if($required)
                <span class="ml-1 text-[#B41F2A]">*</span>
            @endif
        </p>
        
        @if($required)
            <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase transition-colors"
                  :class="unansweredCodes.includes('{{ $question['code'] }}') ? 'bg-[#B41F2A] text-white animate-pulse' : 'bg-red-50 text-[#B41F2A]'">
                <span x-text="unansweredCodes.includes('{{ $question['code'] }}') ? 'Wajib Diisi!' : 'Wajib'"></span>
            </span>
        @endif
    </div>

    <div class="mt-2 pl-6">
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
