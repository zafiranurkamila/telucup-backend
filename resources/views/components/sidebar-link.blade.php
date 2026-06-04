@props([
    'href',
    'icon' => null,
    'active' => false,
])

@php
$classes = $active
    ? 'sidebar-link active'
    : 'sidebar-link';
@endphp

<li>
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} @if($active) aria-current="page" @endif>
        @if($icon)
            <span class="w-5 h-5 shrink-0 {{ $active ? 'text-brand' : 'text-gray-500' }}">
                {!! $icon !!}
            </span>
        @endif
        <span class="leading-tight">{{ $slot }}</span>
    </a>
</li>
