@php
    $isPlayer = request()->user()->role === 'player';
    $roleLabel = $isPlayer ? 'Player' : 'PIC Kontingen';
@endphp

<x-layouts.dashboard :roleLabel="$roleLabel">
    <x-slot:title>Self Assessment</x-slot:title>

    <x-slot:sidebar>
        @if($isPlayer)
            @include('partials.sidebar-player')
        @else
            @include('partials.sidebar-pic')
        @endif
    </x-slot:sidebar>

<main class="min-h-screen bg-[#f4f7f6] flex items-center justify-center p-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-10 max-w-md w-full text-center">
        <div class="w-20 h-20 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <svg class="h-10 w-10 text-[#B41F2A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
        </div>
        <h2 class="text-xl font-extrabold text-gray-900 mb-2">Belum Ada Assessment</h2>
        <p class="text-sm text-gray-500 leading-relaxed mb-6">
            Anda belum pernah mengisi self-assessment kesehatan. Isi sekarang untuk mengetahui
            status risiko Anda sebelum bertanding.
        </p>
        <a href="{{ request()->user()->role === 'player' ? route('dashboard.player.self-assessment.index') : route('dashboard.pic.self-assessment.index') }}"
           class="inline-block w-full bg-[#B41F2A] text-white rounded-lg py-3 font-bold text-sm hover:bg-[#981A24] transition-colors shadow-sm">
            Mulai Self Assessment &rarr;
        </a>
    </div>
</main>
</x-layouts.dashboard>
