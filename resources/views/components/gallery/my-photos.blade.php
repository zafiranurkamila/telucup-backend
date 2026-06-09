<div
    x-show="activeTab === 'foto-saya'"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-4"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-data="fotoSayaGallery()"
    @fs:load.window="if (!loaded) fetchPhotos()"
    style="display: none;"
>
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="flex flex-wrap gap-2">
            <template x-for="(cfg, key) in filterCfg" :key="key">
                <button
                    @click="statusFilter = key"
                    :class="statusFilter === key ? cfg.active : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                    class="px-4 py-1.5 rounded-full text-xs font-bold transition-all"
                >
                    <span x-text="cfg.label"></span>
                    <span class="opacity-70 ml-0.5" x-text="'(' + counts[key] + ')'"></span>
                </button>
            </template>
        </div>
        <button
            @click="fetchPhotos()"
            :disabled="loading"
            class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm text-gray-600 font-medium transition-colors hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed shrink-0"
        >
            <svg class="w-4 h-4" :class="loading && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Refresh
        </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 min-h-[50vh]">
        <template x-if="loading">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <template x-for="i in 8" :key="i">
                    <div class="rounded-xl bg-gray-100 animate-pulse aspect-square"></div>
                </template>
            </div>
        </template>

        <template x-if="!loading && error">
            <div class="flex flex-col items-center justify-center h-64 text-center gap-4">
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center">
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800" x-text="error"></p>
                    <button @click="fetchPhotos()" class="mt-2 text-xs text-[#B41F2A] font-bold hover:underline">Coba Lagi</button>
                </div>
            </div>
        </template>

        <template x-if="!loading && !error && filteredPhotos.length === 0">
            <div class="flex flex-col items-center justify-center h-64 text-center gap-3">
                <div class="w-16 h-16 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center">
                    <svg class="text-gray-300 w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <template x-if="statusFilter === 'all'">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">Belum ada foto yang cocok dengan wajah kamu.</p>
                        <p class="text-xs text-gray-400 mt-1 max-w-xs">Foto akan muncul setelah panitia mengunggah dokumentasi dan AI selesai memproses wajah.</p>
                    </div>
                </template>
                <template x-if="statusFilter !== 'all'">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">Tidak ada foto dengan status ini.</p>
                        <button @click="statusFilter = 'all'" class="mt-2 text-xs text-[#B41F2A] font-bold hover:underline">Lihat semua foto</button>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="!loading && !error && filteredPhotos.length > 0">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <template x-for="photo in filteredPhotos" :key="photo.id">
                    <div
                        class="group relative rounded-xl overflow-hidden border border-gray-100 shadow-sm bg-gray-50 cursor-pointer hover:shadow-md transition-all aspect-square"
                        @click="openModal(photo)"
                    >
                        <img
                            :src="photo.event_photo.image_url"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                            loading="lazy"
                            alt=""
                        >

                        <button
                            type="button"
                            @click.stop="downloadPhoto(photo)"
                            class="absolute top-2 right-2 w-9 h-9 rounded-full bg-white/90 text-gray-700 shadow-sm border border-white/70 flex items-center justify-center opacity-0 group-hover:opacity-100 focus:opacity-100 transition-all hover:bg-white hover:text-[#B41F2A]"
                            title="Download foto"
                            aria-label="Download foto"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 4v12m0 0l-4-4m4 4l4-4"/>
                            </svg>
                        </button>

                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/65 to-transparent px-3 py-2.5 flex items-end justify-between gap-1.5">
                            <span
                                class="text-[10px] font-bold px-2 py-0.5 rounded-full border"
                                :class="statusClass(photo.validation_status)"
                                x-text="statusLabel(photo.validation_status)"
                            ></span>
                        </div>

                        <template x-if="photo.validation_status === 'pending'">
                            <span class="absolute top-2 left-2 w-2.5 h-2.5 rounded-full bg-amber-400 shadow ring-2 ring-white block"></span>
                        </template>
                    </div>
                </template>
            </div>
        </template>
    </div>

    <template x-if="selectedPhoto">
        <div
            class="fixed inset-0 z-[60] bg-black/75 flex items-center justify-center p-4"
            @click.self="closeModal()"
            @keydown.escape.window="closeModal()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div
                class="bg-white rounded-2xl overflow-hidden w-full max-w-3xl shadow-2xl flex flex-col max-h-[92vh]"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <div class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-gray-100 shrink-0">
                    <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#B41F2A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        </svg>
                        Detail Foto
                    </h3>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="downloadPhoto(selectedPhoto)"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-bold text-gray-700 hover:bg-gray-50 hover:text-[#B41F2A] transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 4v12m0 0l-4-4m4 4l4-4"/>
                            </svg>
                            Download
                        </button>
                        <button @click="closeModal()" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors" aria-label="Tutup detail foto">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="relative bg-black overflow-hidden shrink-0">
                    <img
                        :src="selectedPhoto.event_photo.image_url"
                        class="w-full max-h-[62vh] object-contain block"
                        alt=""
                    >
                </div>

                <div class="p-5 space-y-4 overflow-y-auto">
                    <div class="flex flex-wrap gap-3">
                        <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-50 border border-gray-100">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Status</p>
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full border" :class="statusClass(selectedPhoto.validation_status)" x-text="statusLabel(selectedPhoto.validation_status)"></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-50 border border-gray-100">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Tanggal</p>
                                <p class="text-sm font-semibold text-gray-700" x-text="formatDate(selectedPhoto.event_photo.created_at)"></p>
                            </div>
                        </div>
                    </div>

                    <template x-if="selectedPhoto.validation_status === 'pending'">
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-xs text-gray-500 mb-3 font-medium">Apakah foto ini menampilkan dirimu?</p>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <button
                                    @click="validate(selectedPhoto.id, 'accepted')"
                                    :disabled="validating === selectedPhoto.id"
                                    class="flex-1 flex items-center justify-center gap-2 py-2.5 px-4 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Ya, Itu Saya
                                </button>
                                <button
                                    @click="validate(selectedPhoto.id, 'rejected')"
                                    :disabled="validating === selectedPhoto.id"
                                    class="flex-1 flex items-center justify-center gap-2 py-2.5 px-4 bg-white hover:bg-red-50 text-[#B41F2A] border border-red-200 text-sm font-bold rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Bukan Saya
                                </button>
                            </div>
                        </div>
                    </template>

                    <template x-if="selectedPhoto.validation_status === 'accepted'">
                        <div class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
                            <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm font-semibold text-green-700">Foto ini sudah kamu terima sebagai dokumentasimu.</p>
                        </div>
                    </template>

                    <template x-if="selectedPhoto.validation_status === 'rejected'">
                        <div class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                            <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm font-semibold text-gray-600">Kamu telah menandai foto ini sebagai bukan dirimu.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>
