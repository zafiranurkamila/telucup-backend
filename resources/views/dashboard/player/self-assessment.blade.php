<x-layouts.dashboard :roleLabel="'Player'">
    <x-slot:title>Self Assessment</x-slot:title>

    <x-slot:sidebar>
        @include('partials.sidebar-player')
    </x-slot:sidebar>

    <div
        x-data="selfAssessmentPage()"
        x-init="init()"
        class="space-y-6 pb-10"
    >
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Self Assessment</h1>
            <p class="text-gray-500 text-sm mt-1">
                Isi assessment kesehatan sebelum mengikuti pertandingan.
            </p>
        </div>

        <div x-show="isLoading" class="bg-white rounded-xl border p-8 text-center text-gray-500">
            Memuat pertanyaan...
        </div>

        <form
            x-show="!isLoading"
            @submit.prevent="submitAssessment"
            class="space-y-6"
        >
            <template x-for="section in sections" :key="section.code">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-800" x-text="section.title"></h2>
                    <p class="text-sm text-gray-500 mt-1" x-text="section.description"></p>

                    <div class="mt-5 space-y-5">
                        <template x-for="question in section.questions" :key="question.code">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <span x-text="question.text"></span>
                                    <span x-show="question.required" class="text-red-500">*</span>
                                </label>

                                {{-- Boolean --}}
                                <template x-if="question.type === 'boolean'">
                                    <div class="flex gap-3">
                                        <label class="flex items-center gap-2 px-4 py-2 rounded-xl border cursor-pointer">
                                            <input type="radio" :name="question.code" value="true" @change="answers[question.code] = true">
                                            <span class="text-sm">Ya</span>
                                        </label>

                                        <label class="flex items-center gap-2 px-4 py-2 rounded-xl border cursor-pointer">
                                            <input type="radio" :name="question.code" value="false" @change="answers[question.code] = false">
                                            <span class="text-sm">Tidak</span>
                                        </label>
                                    </div>
                                </template>

                                {{-- Integer / Number --}}
                                <template x-if="question.type === 'integer' || question.type === 'number'">
                                    <input
                                        type="number"
                                        class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring-brand"
                                        @input="answers[question.code] = Number($event.target.value)"
                                    >
                                </template>

                                {{-- Select --}}
                                <template x-if="question.type === 'select'">
                                    <select
                                        class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring-brand"
                                        @change="answers[question.code] = $event.target.value"
                                    >
                                        <option value="">Pilih jawaban</option>
                                        <template x-for="option in question.options" :key="option">
                                            <option :value="option" x-text="option"></option>
                                        </template>
                                    </select>
                                </template>

                                {{-- Text --}}
                                <template x-if="question.type === 'string' || question.type === 'text'">
                                    <textarea
                                        rows="3"
                                        class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring-brand"
                                        @input="answers[question.code] = $event.target.value"
                                    ></textarea>
                                </template>

                                {{-- Array / Checkbox --}}
                                <template x-if="question.type === 'array'">
                                    <div class="space-y-2">
                                        <template x-for="option in question.options" :key="option">
                                            <label class="flex items-center gap-2">
                                                <input
                                                    type="checkbox"
                                                    :value="option"
                                                    @change="toggleArrayAnswer(question.code, option, $event.target.checked)"
                                                >
                                                <span class="text-sm text-gray-700" x-text="option"></span>
                                            </label>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="px-6 py-3 rounded-xl bg-[#b71c1c] text-white text-sm font-bold hover:bg-[#9b1818]"
                    :disabled="isSubmitting"
                >
                    <span x-show="!isSubmitting">Submit Assessment</span>
                    <span x-show="isSubmitting">Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('selfAssessmentPage', () => ({
                sections: [],
                answers: {},
                isLoading: true,
                isSubmitting: false,

                async init() {
                    await this.loadQuestionnaire();
                },

                async loadQuestionnaire() {
                    try {
                        const res = await fetch('/api/self-assessment/questionnaire', {
                            headers: { 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        });

                        const json = await res.json();
                        this.sections = json.data.sections || [];
                    } catch (error) {
                        console.error(error);
                        alert('Gagal memuat pertanyaan.');
                    } finally {
                        this.isLoading = false;
                    }
                },

                toggleArrayAnswer(code, value, checked) {
                    if (!Array.isArray(this.answers[code])) {
                        this.answers[code] = [];
                    }

                    if (checked) {
                        this.answers[code].push(value);
                    } else {
                        this.answers[code] = this.answers[code].filter(item => item !== value);
                    }
                },

                async submitAssessment() {
                    this.isSubmitting = true;

                    try {
                        const res = await fetch('/api/self-assessment', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                answers: this.answers,
                            }),
                        });

                        const json = await res.json();

                        if (!res.ok) {
                            throw new Error(json.message || 'Gagal submit assessment.');
                        }

                        alert('Assessment berhasil disimpan.');
                        window.location.href = "{{ route('dashboard.player.profil.show') }}";
                    } catch (error) {
                        console.error(error);
                        alert(error.message);
                    } finally {
                        this.isSubmitting = false;
                    }
                },
            }));
        });
    </script>
</x-layouts.dashboard>