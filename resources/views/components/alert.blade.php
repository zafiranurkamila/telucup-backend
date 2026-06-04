@props([
    'type' => 'info',
    'dismissible' => false,
])

@php
$styles = [
    'success' => 'bg-green-50 text-green-800 border-green-200',
    'error'   => 'bg-red-50 text-red-800 border-red-200',
    'warning' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
    'info'    => 'bg-blue-50 text-blue-800 border-blue-200',
];

$icons = [
    'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'error'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>',
    'info'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
];
@endphp

<div
    {{ $attributes->merge(['class' => "flex items-start gap-3 p-4 rounded-lg border text-sm {$styles[$type]}"]) }}
    @if($dismissible) x-data="{ show: true }" x-show="show" x-transition @endif
>
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {!! $icons[$type] !!}
    </svg>

    <div class="flex-1">{{ $slot }}</div>

    @if($dismissible)
        <button @click="show = false" class="shrink-0 ml-2 opacity-60 hover:opacity-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</div>
