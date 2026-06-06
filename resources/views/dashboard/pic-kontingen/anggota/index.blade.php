<x-layouts.dashboard :roleLabel="'PIC Kontingen'">
    <x-slot:title>Anggota Kontingen</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-pic')
    </x-slot:sidebar>

    <div x-data="anggotaManager" class="space-y-6 pb-10">
        @include('dashboard.pic-kontingen.anggota.partials.header')

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @include('dashboard.pic-kontingen.anggota.partials.table')
        </div>

        @include('dashboard.pic-kontingen.anggota.partials.modals')
    </div>
</x-layouts.dashboard>
