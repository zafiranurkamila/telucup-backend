{{-- Sidebar menu untuk dashboard Player --}}

@php
$currentRoute = request()->path();

$menuItems = [
    [
        'name' => 'Profil Saya',
        'href' => route('dashboard.player.profil.show'),
        'match' => 'dashboard/player/profil',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    ],
    [
        'name' => 'Self Assessment',
        'href' => route('dashboard.player.self-assessment.index'),
        'match' => 'dashboard/player/self-assessment',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>',
    ],
    [
        'name' => 'Galeri Saya',
        'href' => route('dashboard.player.index') . '/galeri',
        'match' => 'dashboard/player/galeri',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
    ],
];
@endphp

@foreach($menuItems as $item)
    @php
        $isActive = str_starts_with($currentRoute, $item['match']);
    @endphp

    <x-sidebar-link :href="$item['href']" :active="$isActive" :icon="$item['icon']">
        {{ $item['name'] }}
    </x-sidebar-link>
@endforeach
