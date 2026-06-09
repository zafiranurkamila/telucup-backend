<x-layouts.dashboard :roleLabel="'Panitia'">
    <x-slot:title>Poster Sportifitas</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-panitia')
    </x-slot:sidebar>

    <div x-data="posterManager" class="space-y-6 pb-10">
        
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-black text-gray-900 tracking-tight">
                Poster <span class="text-[#B41F2A]">Sportifitas</span>
            </h1>
            <p class="text-gray-500 text-sm md:text-base mt-2 max-w-2xl">
                Kelola poster reminder sportifitas yang akan ditampilkan setelah peserta melakukan self-assessment. Aktifkan dan atur urutan poster untuk membuat kompilasi Carousel pada tampilan akhir mereka.
            </p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Poster</p>
                <p class="text-2xl font-black text-gray-900" x-text="posters.length"></p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Poster Aktif</p>
                <p class="text-2xl font-black text-emerald-600" x-text="posters.filter(p => p.is_active).length"></p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Nonaktif</p>
                <p class="text-2xl font-black text-gray-400" x-text="posters.filter(p => !p.is_active).length"></p>
            </div>
            <div class="bg-[#B41F2A] rounded-2xl p-4 shadow-sm shadow-red-200">
                <p class="text-xs font-bold text-red-200 uppercase tracking-wider mb-1">Tampil di Carousel</p>
                <p class="text-2xl font-black text-white" x-text="posters.filter(p => p.is_active).length"></p>
            </div>
        </div>

        {{-- Toolbar --}}
        @include('dashboard.panitia.poster.partials.toolbar')

        {{-- Grid / Reorder --}}
        <template x-if="!isReorderMode">
            @include('dashboard.panitia.poster.partials.grid')
        </template>
        
        <template x-if="isReorderMode">
            @include('dashboard.panitia.poster.partials.reorder-panel')
        </template>

        {{-- Modals --}}
        @include('dashboard.panitia.poster.partials.form-modal')
        @include('dashboard.panitia.poster.partials.delete-modal')
        @include('dashboard.panitia.poster.partials.preview-modal')

    </div>
</x-layouts.dashboard>
