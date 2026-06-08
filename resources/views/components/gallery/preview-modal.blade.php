<div
    class="fixed inset-0 z-50 flex flex-col bg-gray-950/95 backdrop-blur-sm"
    x-show="photoToPreview !== null"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="photoToPreview = null"
    style="display: none;"
>
    <template x-if="photoToPreview">
        <div class="flex flex-col h-full">

            {{-- Top Bar --}}
            <div class="shrink-0 flex items-center justify-between px-4 py-3 bg-black/50 border-b border-white/10">

                {{-- Left: tanggal foto --}}
                <div class="flex items-center gap-2 text-white/60 text-sm select-none">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span x-text="new Date(photoToPreview.created_at).toLocaleDateString('id-ID', {weekday:'long', day:'numeric', month:'long', year:'numeric'})"></span>
                </div>

                {{-- Right: action buttons --}}
                <div class="flex items-center gap-1.5">

                    {{-- Download --}}
                    <button
                        @click="downloadPhoto(photoToPreview.image_url)"
                        title="Unduh foto"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold rounded-lg transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh
                    </button>

                    {{-- Panitia-only actions --}}
                    <template x-if="!isViewer">
                        <div class="flex items-center gap-1.5">
                            <div class="w-px h-5 bg-white/20 mx-0.5"></div>

                            {{-- Pindah folder --}}
                            <button
                                @click="let p = photoToPreview; photoToPreview = null; openMoveModal(p)"
                                title="Pindah folder"
                                class="p-2 bg-white/10 hover:bg-white/20 text-white/80 hover:text-white rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                            </button>

                            {{-- Hapus --}}
                            <button
                                @click="let p = photoToPreview; photoToPreview = null; confirmDeletePhoto(p)"
                                title="Hapus foto"
                                class="p-2 bg-white/10 hover:bg-red-600 text-white/80 hover:text-white rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </template>

                    <div class="w-px h-5 bg-white/20 mx-0.5"></div>

                    {{-- Close --}}
                    <button
                        @click="photoToPreview = null"
                        title="Tutup (Esc)"
                        class="p-2 bg-white/10 hover:bg-white/20 text-white/80 hover:text-white rounded-lg transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Photo area — klik area kosong untuk tutup --}}
            <div class="flex-1 flex items-center justify-center p-6 min-h-0" @click="photoToPreview = null">
                <img
                    :src="photoToPreview.image_url"
                    class="max-w-full max-h-full object-contain rounded-sm shadow-2xl select-none"
                    @click.stop
                    draggable="false"
                >
            </div>

        </div>
    </template>
</div>
