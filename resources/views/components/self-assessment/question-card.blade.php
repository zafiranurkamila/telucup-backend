@props(['question', 'number' => null])

@php
    $required = $question['required'] ?? true;
@endphp

<div id="question-{{ $question['code'] }}"
     class="px-5 py-5 transition-all duration-300 sm:px-6"
     :class="{ 'bg-red-50/60': unansweredCodes.includes('{{ $question['code'] }}') }">
    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(260px,420px)] lg:items-start">
        <div class="flex items-start gap-3">
            @if($number)
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-gray-100 text-xs font-black text-gray-500">
                    {{ $number }}
                </span>
            @endif
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    @if($required)
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wide transition-colors"
                              :class="unansweredCodes.includes('{{ $question['code'] }}') ? 'bg-[#B41F2A] text-white' : 'bg-red-50 text-[#B41F2A]'">
                            <span x-text="unansweredCodes.includes('{{ $question['code'] }}') ? 'Wajib diisi' : 'Wajib'"></span>
                        </span>
                    @else
                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-gray-400">Opsional</span>
                    @endif
                    <span class="text-[11px] font-bold uppercase tracking-wide text-gray-400">{{ $question['code'] }}</span>
                </div>
                <p class="mt-2 text-sm font-bold leading-relaxed text-gray-950">
                    {{ $question['text'] }}
                </p>
            </div>
        </div>

        <div class="lg:pt-0.5">
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
</div>
