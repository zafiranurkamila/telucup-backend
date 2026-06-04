@props([
    'label',
    'value',
    'subtext' => null,
    'icon' => null,
    'iconBg' => 'bg-blue-50',
    'iconColor' => 'text-blue-600',
    'highlight' => false,
])

@php
$borderClass = $highlight
    ? 'border-red-200 ring-1 ring-red-100 shadow-[0_0_15px_rgba(239,68,68,0.1)]'
    : 'border-gray-100';
@endphp

<div {{ $attributes->merge(['class' => "bg-white rounded-xl p-5 border {$borderClass} flex items-start justify-between"]) }}>
    <div>
        <p class="text-sm font-medium text-gray-500 mb-1">{{ $label }}</p>
        <h3 class="text-3xl font-bold text-gray-900 mb-1">{{ $value }}</h3>
        @if($subtext)
            <p class="text-xs text-gray-400">{{ $subtext }}</p>
        @endif
    </div>

    @if($icon)
        <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $iconBg }} {{ $iconColor }}">
            {!! $icon !!}
        </div>
    @endif
</div>
