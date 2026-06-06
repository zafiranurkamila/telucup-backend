<x-layout-public>
    <x-slot:title>Galeri - Tel-U Cup</x-slot:title>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 relative" x-data="galeriManager(true)">
        
        <!-- Header Text -->
        <div class="text-center mb-12">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-50 text-[#b6252a] text-sm font-medium mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Dokumentasi Resmi
            </span>
            <h1 class="text-4xl md:text-5xl font-black text-gray-900 tracking-tight mb-4">Galeri Tel-U Cup 2026</h1>
            <p class="text-lg text-gray-500 max-w-2xl mx-auto">Jelajahi momen-momen terbaik dan sorotan pertandingan dari seluruh cabang olahraga.</p>
        </div>

        {{-- Toast --}}
        <template x-if="toast">
            <div class="fixed top-24 right-6 z-[100] flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg font-medium transition-all"
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

        {{-- Error Banner --}}
        <template x-if="error && !loading">
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-3 text-sm mb-6 max-w-4xl mx-auto">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="flex-1" x-text="error"></p>
                <button @click="fetchData()" class="px-3 py-1 bg-red-100 hover:bg-red-200 rounded font-medium transition-colors">Coba Lagi</button>
            </div>
        </template>

        <div class="max-w-6xl mx-auto space-y-6">
            {{-- Toolbar Reusable Component --}}
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
        </div>

        {{-- Modals needed for viewing --}}
        @include('components.gallery.preview-modal')
    </div>
</x-layout-public>
