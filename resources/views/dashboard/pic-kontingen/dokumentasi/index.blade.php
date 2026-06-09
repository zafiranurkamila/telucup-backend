<x-layouts.dashboard :roleLabel="'PIC Kontingen'">
    <x-slot:title>Dokumentasi Event</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-pic')
    </x-slot:sidebar>

    @include('components.gallery.user-gallery-tabs')
</x-layouts.dashboard>
