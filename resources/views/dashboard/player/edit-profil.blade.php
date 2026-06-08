<x-layouts.dashboard :roleLabel="'Player'">
    <x-slot:title>Edit Profil</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-player')
    </x-slot:sidebar>

    <div class="space-y-6 pb-10 max-w-3xl">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Profil</h1>
            <p class="text-gray-500 text-sm mt-1">Perbarui data profil peserta kamu.</p>
        </div>

        <form
            action="{{ route('dashboard.player.profil.update') }}"
            method="POST"
            enctype="multipart/form-data"
            class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5"
        >
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring-brand"
                    required
                >
                @error('name')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">NIM/NIP</label>
                <input
                    type="text"
                    name="nim_nip"
                    value="{{ old('nim_nip', $player?->nim_nip) }}"
                    class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring-brand"
                >
                @error('nim_nip')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">No. HP</label>
                <input
                    type="text"
                    name="phone"
                    value="{{ old('phone', $player?->phone) }}"
                    class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring-brand"
                >
                @error('phone')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Profil</label>

                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-2xl bg-gray-100 border border-gray-200 overflow-hidden flex items-center justify-center">
                        @if($player?->photo_path)
                            <img src="{{ asset('storage/' . $player->photo_path) }}" class="w-full h-full object-cover" alt="Foto profil">
                        @else
                            <span class="text-2xl font-black text-gray-400">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                        @endif
                    </div>

                    <input
                        type="file"
                        name="photo"
                        accept="image/*"
                        class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-red-50 file:text-[#b71c1c] file:font-bold hover:file:bg-red-100"
                    >
                </div>

                @error('photo')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a
                    href="{{ route('dashboard.player.profil.show') }}"
                    class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="px-5 py-2 rounded-xl bg-[#b71c1c] text-white text-sm font-bold hover:bg-[#9b1818]"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>