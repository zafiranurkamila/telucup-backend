<x-layouts.dashboard :roleLabel="'Player'">
    <x-slot:title>Dokumentasi Event</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-player')
    </x-slot:sidebar>

    @include('components.gallery.user-gallery-tabs')
</x-layouts.dashboard>
