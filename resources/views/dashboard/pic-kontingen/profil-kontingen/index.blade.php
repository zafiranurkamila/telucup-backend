<x-layouts.dashboard :roleLabel="'PIC Kontingen'">
    <x-slot:title>Profil Kontingen</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-pic')
    </x-slot:sidebar>

    <div class="space-y-6 pb-10" x-data="{ isUploading: false, isDeleting: false }">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="fixed bottom-4 right-4 flex items-center gap-2 px-4 py-3 rounded-lg shadow-lg z-50 text-white bg-emerald-600" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
                <button @click="show = false" class="ml-2 hover:opacity-75">&times;</button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="fixed bottom-4 right-4 flex items-center gap-2 px-4 py-3 rounded-lg shadow-lg z-50 text-white bg-red-600" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-medium">
                    {{ session('error') ?? $errors->first() }}
                </span>
                <button @click="show = false" class="ml-2 hover:opacity-75">&times;</button>
            </div>
        @endif

        @if(!$contingent)
            <div class="flex justify-center items-center h-96">
                <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200 text-center max-w-md">
                    <svg class="w-12 h-12 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Kontingen Tidak Ditemukan</h2>
                    <p class="text-gray-500 mb-6">
                        Anda belum terdaftar atau ditugaskan sebagai PIC di kontingen manapun.
                    </p>
                </div>
            </div>
        @else
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Profil Kontingen</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Kelola identitas dan informasi kontingen Anda.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Card: Kontingen Profile --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden lg:col-span-2">
                    <div class="h-24 bg-gradient-to-r from-[#8a1519] to-[#c21e24]"></div>

                    <div class="px-6 sm:px-8 pb-8">
                        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 -mt-12">
                            {{-- Logo Upload Section --}}
                            <div class="relative group">
                                <div class="w-28 h-28 sm:w-32 sm:h-32 bg-white rounded-2xl shadow-md border-4 border-white flex items-center justify-center overflow-hidden relative">
                                    @if($contingent->image_url)
                                        <img src="{{ $contingent->image_url }}" alt="{{ $contingent->name }}" class="w-full h-full object-cover" />
                                    @else
                                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    @endif

                                    {{-- Upload Overlay --}}
                                    <form action="{{ route('dashboard.pic.kontingen.updateLogo') }}" method="POST" enctype="multipart/form-data" x-ref="uploadForm" class="absolute inset-0">
                                        @csrf
                                        <label 
                                            class="w-full h-full bg-black/60 flex flex-col items-center justify-center cursor-pointer transition-opacity text-white opacity-0 group-hover:opacity-100"
                                            :class="{ 'opacity-100': isUploading }"
                                        >
                                            <template x-if="isUploading">
                                                <svg class="animate-spin mb-1 w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            </template>
                                            <template x-if="!isUploading">
                                                <svg class="mb-1 w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </template>
                                            
                                            <span class="text-[10px] font-medium tracking-wider uppercase" x-text="isUploading ? 'Mengunggah...' : 'Ubah Logo'"></span>
                                            
                                            <input 
                                                type="file" 
                                                name="image" 
                                                accept="image/jpeg,image/png,image/jpg" 
                                                class="hidden" 
                                                @change="if($event.target.files.length > 0) { isUploading = true; $refs.uploadForm.submit(); }"
                                                :disabled="isUploading"
                                            />
                                        </label>
                                    </form>
                                </div>

                                @if($contingent->image_url)
                                    <form action="{{ route('dashboard.pic.kontingen.deleteLogo') }}" method="POST" x-ref="deleteForm" x-show="!isUploading">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="button"
                                            @click="if(confirm('Apakah Anda yakin ingin menghapus logo kontingen?')) { isDeleting = true; $refs.deleteForm.submit(); }"
                                            :disabled="isDeleting"
                                            class="absolute -top-2 -right-2 bg-white text-red-500 hover:text-white hover:bg-red-500 p-1.5 rounded-full shadow-md border border-gray-100 transition-colors z-10"
                                            title="Hapus Logo"
                                        >
                                            <template x-if="isDeleting">
                                                <svg class="animate-spin w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            </template>
                                            <template x-if="!isDeleting">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </template>
                                        </button>
                                    </form>
                                @endif
                            </div>

                            {{-- Kontingen Details --}}
                            <div class="flex-1 text-center sm:text-left mt-2 sm:mt-14">
                                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">
                                    {{ $contingent->name }}
                                </h2>
                                <p class="text-sm font-medium text-gray-500 mt-1 flex items-center justify-center sm:justify-start gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> Dikelola oleh Anda
                                </p>

                                <div class="mt-5 flex flex-wrap justify-center sm:justify-start gap-3">
                                    <div class="bg-gray-50 border border-gray-100 rounded-lg px-4 py-2 flex items-center gap-3">
                                        <div class="bg-blue-100 text-blue-600 p-1.5 rounded-md">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-[10px] uppercase font-bold text-gray-400">Total Pemain</p>
                                            <p class="text-sm font-bold text-gray-800">
                                                {{ $contingent->players_count }} Terdaftar
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: PIC Detail --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sm:p-8 lg:col-span-2">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-red-50 text-[#b71c1c] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        Informasi PIC Utama
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <p class="text-xs font-bold uppercase text-gray-400 mb-1">Nama Lengkap</p>
                            <p class="font-semibold text-gray-800">{{ $contingent->pic->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase text-gray-400 mb-1">Email</p>
                            <p class="font-semibold text-gray-800 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $contingent->pic->email ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase text-gray-400 mb-1">Peran Akses</p>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-purple-50 text-purple-700 border border-purple-100 uppercase tracking-wider">
                                {{ $contingent->pic->role ?? '-' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase text-gray-400 mb-1">Pengguna Kacamata Olahraga</p>
                            <p class="font-semibold text-gray-800">
                                {{ optional($contingent->pic)->is_kacamata ? 'Ya' : 'Tidak' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.dashboard>
