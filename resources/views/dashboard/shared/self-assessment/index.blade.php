@php
    $isPlayer = request()->user()->role === 'player';
    $roleLabel = $isPlayer ? 'Player' : 'PIC Kontingen';
@endphp

<x-layouts.onboarding>
    <x-slot:title>Self Assessment</x-slot:title>

<section class="min-h-screen bg-white" x-data="selfAssessmentForm(@js($questionnaire), @js($activePosters))" x-cloak>
    <div class="mx-auto max-w-5xl px-5 py-10 sm:px-8 lg:py-16">
        <button type="button" @click="window.history.back()" class="mb-12 inline-flex items-center gap-2 text-sm font-semibold text-gray-500 transition-colors hover:text-[#B41F2A]">
            <span class="text-lg leading-none">&larr;</span> Kembali
        </button>

        <div class="mb-16 max-w-3xl">
            <h1 class="text-3xl font-black leading-tight text-gray-950 sm:text-4xl">Self Assessment Kesehatan Pemain</h1>
            <p class="mt-7 text-lg leading-relaxed text-gray-500">
                Isi kondisi kesehatan Anda dengan jujur untuk membantu memastikan keselamatan selama sesi latihan dan pertandingan.
            </p>
            <div class="mt-8 flex flex-wrap gap-4">
                <span class="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-500">
                    <svg class="h-4 w-4 text-[#B41F2A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z" />
                    </svg>
                    Perkiraan: 3-6 menit
                </span>
                <span class="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-500">
                    <svg class="h-4 w-4 text-[#B41F2A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V8a4.5 4.5 0 0 0-9 0v2.5m-.75 0h10.5a1.5 1.5 0 0 1 1.5 1.5v5.25a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5V12a1.5 1.5 0 0 1 1.5-1.5Z" />
                    </svg>
                    Data rahasia & terenkripsi
                </span>
            </div>
        </div>

        <form @submit.prevent="submitForm" novalidate class="space-y-8">
            @foreach($questionnaire['sections'] as $section)
            <x-self-assessment.section
                :title="$section['domain'] === 'DEMO' ? $section['title'] : 'Bagian ' . ($section['code'] ?? $section['domain']) . ' - ' . $section['title']"
                :subtitle="$section['description'] ?? null"
                :code="$section['code'] ?? $section['domain'] ?? null"
            >
                @foreach($section['questions'] as $q)
                <x-self-assessment.question-card :question="$q" :number="$loop->iteration" />
                @endforeach
            </x-self-assessment.section>
            @endforeach

            <x-self-assessment.consent-section />

            <div x-show="submitError" x-transition class="rounded-lg border border-red-200 bg-red-50 p-4 mb-6">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 text-red-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-red-800">Gagal Mengirim Assessment</h3>
                        <p class="mt-1 text-sm text-red-700" x-text="submitError"></p>
                    </div>
                </div>
            </div>

            <x-self-assessment.form-actions />
        </form>

        <x-self-assessment.modals.announcement />
        <x-self-assessment.modals.sportsmanship-reminder />
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('selfAssessmentForm', (questionnaire, activePosters) => ({
        answers: {},
        unansweredCodes: [],
        isSubmitting: false,
        submitError: null,
        showAnnouncementModal: false,
        showReminderModal: false,
        posters: activePosters || [],
        currentPosterIndex: 0,
        hasReachedEnd: false,
        submittedId: null,

        init() {
            questionnaire.sections.forEach(section => {
                section.questions.forEach(q => {
                    if (q.type === 'multi_choice') {
                        this.answers[q.code] = [];
                    } else if (q.type === 'boolean') {
                        this.answers[q.code] = null;
                    } else {
                        this.answers[q.code] = '';
                    }
                });
            });

            if (this.posters.length <= 1) {
                this.hasReachedEnd = true;
            }

            this.$watch('currentPosterIndex', value => {
                if (value === this.posters.length - 1) {
                    this.hasReachedEnd = true;
                }
            });
        },

        validate() {
            this.unansweredCodes = [];
            let isValid = true;

            questionnaire.sections.forEach(section => {
                section.questions.forEach(q => {
                    if (q.required !== false) {
                        const val = this.answers[q.code];
                        const isUnanswered = val === null || val === '' || val === undefined || (Array.isArray(val) && val.length === 0);
                        if (isUnanswered) {
                            this.unansweredCodes.push(q.code);
                            isValid = false;
                        }
                    }
                });
            });

            if (!isValid) {
                const firstErrorId = `question-${this.unansweredCodes[0]}`;
                const el = document.getElementById(firstErrorId);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            return isValid;
        },

        async submitForm() {
            this.submitError = null;
            if (!this.validate()) {
                this.submitError = 'Mohon lengkapi semua pertanyaan yang wajib diisi terlebih dahulu.';
                return;
            }

            if (!this.answers['consent']) {
                this.submitError = 'Anda harus menyetujui pernyataan persetujuan (consent) untuk melanjutkan.';
                return;
            }

            this.isSubmitting = true;

            const payload = { answers: { ...this.answers } };
            delete payload.answers.consent;

            try {
                const response = await fetch('/api/self-assessment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Terjadi kesalahan saat mengirim assessment.');
                }

                this.submittedId = result.data.id;
                this.showAnnouncementModal = true;
            } catch (error) {
                this.submitError = error.message;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } finally {
                this.isSubmitting = false;
            }
        },

        proceedToResult() {
            this.showAnnouncementModal = false;
            if (this.posters.length > 0) {
                this.showReminderModal = true;
            } else {
                this.goToResultPage();
            }
        },

        goToResultPage() {
            window.location.href = window.location.pathname + '/hasil' + (this.submittedId ? '?id=' + this.submittedId : '');
        },

        nextPoster() {
            if (this.currentPosterIndex < this.posters.length - 1) {
                this.currentPosterIndex++;
            }
        },

        prevPoster() {
            if (this.currentPosterIndex > 0) {
                this.currentPosterIndex--;
            }
        }
    }));
});
</script>
@endpush
</x-layouts.onboarding>
