<x-layouts.dashboard :roleLabel="'PIC Kontingen'">
    <x-slot:title>Registrasi Tim</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-pic')
    </x-slot:sidebar>

    <div x-data="registrasiManager" class="space-y-6 pb-10">
        @include('dashboard.pic-kontingen.registrasi.partials.header')

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @include('dashboard.pic-kontingen.registrasi.partials.grid')
        </div>

        @include('dashboard.pic-kontingen.registrasi.partials.modals')
    </div>
</x-layouts.dashboard>
