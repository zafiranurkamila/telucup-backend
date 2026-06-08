<x-layouts.dashboard :roleLabel="'Player'">
    <x-slot:title>Galeri Saya</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-player')
    </x-slot:sidebar>

    @php
        $photos = $photos ?? collect();
    @endphp

    <div class="space-y-6 pb-10">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Galeri Saya</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Foto-foto kamu yang terdeteksi dari dokumentasi Tel-U Cup.
                </p>
            </div>

            <a
                href="{{ route('dashboard.player.galeri.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors shadow-sm shrink-0"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M20 9A8 8 0 006.34 4.34L4 6.68M4 15a8 8 0 0013.66 4.66L20 17.32"/>
                </svg>
                Refresh
            </a>
        </div>

        {{-- Info Card --}}
        <div class="bg-gradient-to-r from-[#8a1519] to-[#c21e24] rounded-2xl p-6 text-white shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <p class="text-white/70 text-sm font-medium">Total Foto Terdeteksi</p>
                    <h2 class="text-4xl font-black mt-1">{{ $photos->count() }}</h2>
                    <p class="text-white/80 text-sm mt-2">
                        Foto yang muncul di sini adalah hasil deteksi dari galeri acara.
                    </p>
                </div>

                <div class="w-16 h-16 rounded-2xl bg-white/15 flex items-center justify-center">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Gallery --}}
        @if($photos->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($photos as $photo)
                    @php
                        $imageUrl = $photo->image_url
                            ?? $photo->url
                            ?? $photo->photo_url
                            ?? ($photo->path ? asset('storage/' . $photo->path) : null);

                        $confidence = $photo->confidence ?? $photo->score ?? null;
                    @endphp

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden group">
                        <div class="aspect-[4/3] bg-gray-100 overflow-hidden">
                            @if($imageUrl)
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="Foto galeri"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                >
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4-4a3 3 0 014 0l1 1 2-2a3 3 0 014 0l1 1M4 6h16M4 6v12h16V6"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-bold text-gray-800 text-sm">
                                        {{ $photo->folder?->name ?? $photo->event_name ?? 'Dokumentasi Tel-U Cup' }}
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $photo->created_at?->translatedFormat('d F Y') ?? 'Tanggal tidak tersedia' }}
                                    </p>
                                </div>

                                @if(!is_null($confidence))
                                    <span class="shrink-0 px-2 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        {{ round($confidence * 100) }}%
                                    </span>
                                @endif
                            </div>

                            @if($imageUrl)
                                <a
                                    href="{{ $imageUrl }}"
                                    target="_blank"
                                    class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-[#b71c1c] hover:underline"
                                >
                                    Lihat Foto →
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-purple-50 flex items-center justify-center text-purple-500">
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>

                <h3 class="text-lg font-bold text-gray-700 mb-2">
                    Belum Ada Foto
                </h3>

                <p class="text-sm text-gray-500 max-w-md mx-auto">
                    Foto kamu belum terdeteksi di galeri acara. Nanti kalau sudah ada dokumentasi yang cocok, fotonya akan tampil di sini.
                </p>
            </div>
        @endif
    </div>
</x-layouts.dashboard>