<div x-data="{ activeTab: 'semua' }" class="space-y-6 pb-10">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <svg class="text-[#B41F2A] w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Dokumentasi Event
        </h1>
        <p class="text-gray-500 text-sm mt-1">
            Lihat seluruh dokumentasi Tel-U Cup dan temukan foto yang menampilkan diri kamu.
        </p>
    </div>

    <div class="bg-white p-1 rounded-xl border border-gray-200 shadow-sm inline-flex flex-wrap gap-1">
        <button
            @click="activeTab = 'semua'"
            :class="activeTab === 'semua' ? 'bg-red-50 text-red-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
            class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium transition-all"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Semua Dokumentasi
        </button>
        <button
            @click="activeTab = 'foto-saya'; $dispatch('fs:load')"
            :class="activeTab === 'foto-saya' ? 'bg-red-50 text-red-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
            class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium transition-all"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Foto Saya
        </button>
    </div>

    <div class="mt-6">
        <div
            x-show="activeTab === 'semua'"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            style="display: none;"
        >
            <div x-data="galeriManager(true)" class="space-y-6 relative">
                <template x-if="toast">
                    <div
                        class="fixed top-6 right-6 z-[100] flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg font-medium transition-all"
                        :class="toast.type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'"
                    >
                        <template x-if="toast.type === 'success'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                        <template x-if="toast.type === 'error'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                        <span class="text-sm" x-text="toast.message"></span>
                    </div>
                </template>

                <template x-if="error && !loading">
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-3 text-sm">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="flex-1" x-text="error"></p>
                        <button @click="fetchData()" class="px-3 py-1 bg-red-100 hover:bg-red-200 rounded font-medium transition-colors">Coba Lagi</button>
                    </div>
                </template>

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

                @include('components.gallery.preview-modal')
            </div>
        </div>

        @include('components.gallery.my-photos')
    </div>
</div>
