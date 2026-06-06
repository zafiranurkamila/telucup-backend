<x-layouts.dashboard :roleLabel="'Panitia'">
    <x-slot:title>Kelola Galeri Event</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-panitia')
    </x-slot:sidebar>

    <div x-data="galeriManager(false)" class="space-y-6 pb-10 relative">
        
        {{-- Toast --}}
        <template x-if="toast">
            <div class="fixed top-6 right-6 z-[100] flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg font-medium transition-all"
                 :class="toast.type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'">
                <template x-if="toast.type === 'success'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
                <span class="text-sm" x-text="toast.message"></span>
            </div>
        </template>

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <svg class="text-[#B41F2A] w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Kelola Galeri Event
                </h1>
                <p class="text-gray-500 text-sm mt-1">Kelola folder dan dokumentasi foto kegiatan Tel-U Cup.</p>
            </div>
        </div>

        {{-- Error Banner --}}
        <template x-if="error && !loading">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-3 text-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="flex-1" x-text="error"></p>
                <button @click="fetchData()" class="px-3 py-1 bg-red-100 hover:bg-red-200 rounded font-medium transition-colors">Coba Lagi</button>
            </div>
        </template>

        {{-- Reusable Components from components/gallery --}}
        @include('components.gallery.toolbar')

        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm min-h-[50vh]">
            @include('components.gallery.breadcrumb')

            <template x-if="loading">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                    <template x-for="i in 4" :key="i">
                        <div class="bg-gray-100 rounded-xl h-20 animate-pulse"></div>
                    </template>
                </div>
            </template>

            <template x-if="!loading">
                <div class="mt-6 space-y-10">
                    
                    <template x-if="filteredFolders.length > 0 || (!searchQuery && folders.length === 0)">
                        @include('components.gallery.folder-grid')
                    </template>

                    <template x-if="filteredPhotos.length > 0 || (!searchQuery && photos.length === 0 && currentFolderId !== null)">
                        @include('components.gallery.photo-grid')
                    </template>

                    <template x-if="searchQuery && filteredFolders.length === 0 && filteredPhotos.length === 0">
                        <div class="text-center py-12">
                            <p class="text-gray-500">Tidak ada hasil pencarian untuk "<span x-text="searchQuery"></span>"</p>
                        </div>
                    </template>

                </div>
            </template>
        </div>

        {{-- Modals --}}
        @include('components.gallery.folder-modal')
        @include('components.gallery.upload-modal')
        @include('components.gallery.delete-modal')
        @include('components.gallery.preview-modal')
        @include('components.gallery.move-modal')

    </div>
</x-layouts.dashboard>
