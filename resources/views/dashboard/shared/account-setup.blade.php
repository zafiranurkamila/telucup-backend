@php
    $profilePhoto = $player?->photo_path;
@endphp

<x-layouts.onboarding>
    <x-slot:title>Lengkapi Akun</x-slot:title>

    <div
        class="mx-auto max-w-5xl space-y-6 pb-10"
        x-data="accountSetupForm({
            nextUrl: @js($nextUrl),
            hasPhoto: @js(filled($profilePhoto)),
            initialPhotoUrl: @js($profilePhoto),
            initialIsKacamata: @js((bool) $user->is_kacamata)
        })"
    >
        <div class="flex flex-col gap-2">
            <p class="text-sm font-bold uppercase tracking-wide text-[#B41F2A]">Onboarding Akun</p>
            <h1 class="text-2xl font-bold text-gray-900">Lengkapi Data Akun</h1>
            <p class="max-w-2xl text-sm leading-6 text-gray-500">
                Data ini diperlukan sebelum Anda masuk dashboard dan mengisi self-assessment.
            </p>
        </div>

        <div class="grid gap-5 lg:grid-cols-[1fr_340px] lg:items-start">
            <form @submit.prevent="submit" class="space-y-5 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <div x-show="message" x-transition class="rounded-lg border p-4 text-sm" :class="messageType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700'">
                    <p class="font-semibold" x-text="message"></p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="nim_nip" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-500">NIM / NIP</label>
                        <input
                            id="nim_nip"
                            type="text"
                            x-model="form.nim_nip"
                            required
                            class="w-full rounded-lg border-gray-200 text-sm shadow-sm focus:border-[#B41F2A] focus:ring-[#B41F2A]"
                            placeholder="1301234567"
                        >
                        <p x-show="errors.nim_nip" class="mt-1 text-xs text-red-600" x-text="errors.nim_nip"></p>
                    </div>

                    <div>
                        <label for="employee_status" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-500">Status</label>
                        <select
                            id="employee_status"
                            x-model="form.employee_status"
                            required
                            class="w-full rounded-lg border-gray-200 text-sm shadow-sm focus:border-[#B41F2A] focus:ring-[#B41F2A]"
                        >
                            <option value="">Pilih status</option>
                            <option>Mahasiswa</option>
                            <option>Pegawai Tetap</option>
                            <option>TPA</option>
                            <option>Dosen</option>
                            <option>Tenaga Kependidikan</option>
                        </select>
                        <p x-show="errors.employee_status" class="mt-1 text-xs text-red-600" x-text="errors.employee_status"></p>
                    </div>

                    <div>
                        <label for="work_location" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-500">Lokasi Kerja / Kampus</label>
                        <input
                            id="work_location"
                            type="text"
                            x-model="form.work_location"
                            required
                            class="w-full rounded-lg border-gray-200 text-sm shadow-sm focus:border-[#B41F2A] focus:ring-[#B41F2A]"
                            placeholder="Kampus A"
                        >
                        <p x-show="errors.work_location" class="mt-1 text-xs text-red-600" x-text="errors.work_location"></p>
                    </div>

                    <div>
                        <p class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-500">Pengguna Kacamata</p>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex cursor-pointer items-center justify-center rounded-lg border px-3 py-2 text-sm font-semibold transition" :class="form.is_kacamata === true ? 'border-[#B41F2A] bg-red-50 text-[#B41F2A]' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                <input type="radio" class="sr-only" name="is_kacamata" @change="form.is_kacamata = true">
                                Ya
                            </label>
                            <label class="flex cursor-pointer items-center justify-center rounded-lg border px-3 py-2 text-sm font-semibold transition" :class="form.is_kacamata === false ? 'border-[#B41F2A] bg-red-50 text-[#B41F2A]' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                <input type="radio" class="sr-only" name="is_kacamata" @change="form.is_kacamata = false">
                                Tidak
                            </label>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-5">
                    <h2 class="mb-3 text-sm font-bold text-gray-800">Enroll Face</h2>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="h-24 w-24 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                            <template x-if="photoPreviewUrl">
                                <img :src="photoPreviewUrl" alt="Foto wajah" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!photoPreviewUrl">
                                <div class="flex h-full w-full items-center justify-center text-gray-400">
                                    <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </template>
                        </div>
                        <div class="flex-1">
                            <label for="photo" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-500">
                                Foto Wajah {{ $profilePhoto ? '(opsional jika tidak ingin mengganti)' : '' }}
                            </label>
                            <input
                                id="photo"
                                type="file"
                                accept="image/jpeg,image/png,image/jpg"
                                @change="handlePhotoChange($event)"
                                :required="!hasPhoto"
                                class="block w-full cursor-pointer rounded-lg border border-gray-200 bg-white text-sm text-gray-600 file:mr-4 file:border-0 file:bg-[#B41F2A] file:px-4 file:py-2.5 file:text-sm file:font-bold file:text-white hover:file:bg-[#8A1520]"
                            >
                            <p class="mt-2 text-xs leading-5 text-gray-500">Gunakan foto wajah yang jelas. Format JPG atau PNG, maksimal 5MB.</p>
                            <p x-show="hasPhoto && !form.photo" class="mt-1 text-xs font-semibold text-emerald-600">Foto wajah sudah tersimpan.</p>
                            <p x-show="form.photo" class="mt-1 text-xs font-semibold text-emerald-600">Preview foto siap diupload.</p>
                            <p x-show="errors.photo" class="mt-1 text-xs text-red-600" x-text="errors.photo"></p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-5">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h2 class="text-sm font-bold text-gray-800">Ubah Password</h2>
                        <span class="text-xs font-semibold text-gray-400">Opsional</span>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label for="current_password" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-500">Password Saat Ini</label>
                            <input id="current_password" type="password" x-model="form.current_password" class="w-full rounded-lg border-gray-200 text-sm shadow-sm focus:border-[#B41F2A] focus:ring-[#B41F2A]">
                            <p x-show="errors.current_password" class="mt-1 text-xs text-red-600" x-text="errors.current_password"></p>
                        </div>
                        <div>
                            <label for="password" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-500">Password Baru</label>
                            <input id="password" type="password" x-model="form.password" class="w-full rounded-lg border-gray-200 text-sm shadow-sm focus:border-[#B41F2A] focus:ring-[#B41F2A]">
                            <p x-show="errors.password" class="mt-1 text-xs text-red-600" x-text="errors.password"></p>
                        </div>
                        <div>
                            <label for="password_confirmation" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-gray-500">Konfirmasi</label>
                            <input id="password_confirmation" type="password" x-model="form.password_confirmation" class="w-full rounded-lg border-gray-200 text-sm shadow-sm focus:border-[#B41F2A] focus:ring-[#B41F2A]">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:items-center sm:justify-end">
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#B41F2A] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#8A1520] disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <svg x-show="isSubmitting" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span x-text="submitLabel"></span>
                    </button>
                </div>
            </form>

            <aside class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold text-gray-900">Checklist</h2>
                <div class="mt-4 space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Data identitas</p>
                            <p class="text-xs leading-5 text-gray-500">NIM/NIP, status, lokasi, dan status kacamata.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full" :class="hasPhoto ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                            <template x-if="hasPhoto">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="!hasPhoto">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4m0 4h.01"/></svg>
                            </template>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Enroll face</p>
                            <p class="text-xs leading-5 text-gray-500">Foto dipakai untuk fitur galeri dan verifikasi wajah.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-gray-100 text-gray-500">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5h6m-6 4h6m-6 4h3"/></svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Self assessment</p>
                            <p class="text-xs leading-5 text-gray-500">Setelah akun lengkap, Anda akan diarahkan ke kuesioner kesehatan.</p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('accountSetupForm', ({ nextUrl, hasPhoto, initialPhotoUrl, initialIsKacamata }) => ({
            nextUrl,
            hasPhoto,
            photoPreviewUrl: initialPhotoUrl || '',
            temporaryPhotoPreviewUrl: '',
            isSubmitting: false,
            message: '',
            messageType: 'success',
            errors: {},
            form: {
                nim_nip: @js($player?->nim_nip ?? ''),
                employee_status: @js($player?->employee_status ?? ''),
                work_location: @js($player?->work_location ?? ''),
                is_kacamata: initialIsKacamata,
                current_password: '',
                password: '',
                password_confirmation: '',
                photo: null,
            },

            submitLabel: 'Simpan dan Lanjutkan',

            async submit() {
                this.isSubmitting = true;
                this.message = '';
                this.errors = {};

                try {
                    this.submitLabel = 'Menyimpan data...';
                    await this.updateProfile();

                    if (this.form.photo) {
                        this.submitLabel = 'Mengunggah foto...';
                        await this.enrollFace();
                    } else if (!this.hasPhoto) {
                        throw new Error('Foto wajah wajib diunggah untuk enroll face.');
                    }

                    this.submitLabel = 'Berhasil!';
                    this.messageType = 'success';
                    this.message = 'Data akun dan foto wajah berhasil disimpan. Mengarahkan ke tahap berikutnya...';
                    setTimeout(() => {
                        window.location.href = this.nextUrl;
                    }, 700);
                } catch (error) {
                    this.messageType = 'error';
                    this.message = error.message || 'Gagal menyimpan data akun.';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    this.submitLabel = 'Simpan dan Lanjutkan';
                } finally {
                    this.isSubmitting = false;
                }
            },

            handlePhotoChange(event) {
                const file = event.target.files[0] || null;
                this.form.photo = file;
                this.errors.photo = '';

                if (this.temporaryPhotoPreviewUrl) {
                    URL.revokeObjectURL(this.temporaryPhotoPreviewUrl);
                    this.temporaryPhotoPreviewUrl = '';
                }

                if (!file) {
                    this.photoPreviewUrl = this.hasPhoto ? initialPhotoUrl || '' : '';
                    return;
                }

                this.temporaryPhotoPreviewUrl = URL.createObjectURL(file);
                this.photoPreviewUrl = this.temporaryPhotoPreviewUrl;
            },

            async updateProfile() {
                const payload = {
                    nim_nip: this.form.nim_nip,
                    is_kacamata: this.form.is_kacamata,
                    employee_status: this.form.employee_status,
                    work_location: this.form.work_location,
                };

                if (this.form.password) {
                    payload.current_password = this.form.current_password;
                    payload.password = this.form.password;
                    payload.password_confirmation = this.form.password_confirmation;
                }

                const response = await fetch('/api/player/profile', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify(payload),
                });

                await this.handleResponse(response);
            },

            async enrollFace() {
                const payload = new FormData();
                payload.append('photo', this.form.photo);

                const response = await fetch('/api/players/enroll-face', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: payload,
                });

                const result = await this.handleResponse(response);
                const uploadedPhotoUrl = result?.data?.photo_url;

                if (uploadedPhotoUrl) {
                    if (this.temporaryPhotoPreviewUrl) {
                        URL.revokeObjectURL(this.temporaryPhotoPreviewUrl);
                        this.temporaryPhotoPreviewUrl = '';
                    }
                    this.photoPreviewUrl = uploadedPhotoUrl;
                }

                this.hasPhoto = true;
                this.form.photo = null;
            },

            async handleResponse(response) {
                const result = await response.json().catch(() => ({}));

                if (response.ok) {
                    return result;
                }

                if (result.errors) {
                    this.errors = Object.fromEntries(
                        Object.entries(result.errors).map(([key, value]) => [key, Array.isArray(value) ? value[0] : value])
                    );
                }

                throw new Error(result.message || 'Terjadi kesalahan saat memproses data.');
            },
        }));
    });
    </script>
    @endpush
</x-layouts.onboarding>
