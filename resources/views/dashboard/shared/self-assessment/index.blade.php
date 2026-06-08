@php
    $isPlayer = request()->user()->role === 'player';
    $roleLabel = $isPlayer ? 'Player' : 'PIC Kontingen';
@endphp

<x-layouts.dashboard :roleLabel="$roleLabel">
    <x-slot:title>Self Assessment</x-slot:title>

    <x-slot:sidebar>
        @if($isPlayer)
            @include('partials.sidebar-player')
        @else
            @include('partials.sidebar-pic')
        @endif
    </x-slot:sidebar>

<main class="min-h-screen bg-[#f4f7f6] rounded-xl overflow-hidden" x-data="selfAssessmentForm(@js($questionnaire), @js($activePosters))" x-cloak>
    <div class="mx-auto max-w-4xl px-4 py-8 lg:px-8 lg:py-10">
        <button type="button" @click="window.history.back()" class="mb-6 flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-[#B41F2A] transition-colors">
            <span class="text-lg leading-none">&larr;</span> Kembali
        </button>

        <form @submit.prevent="submitForm" novalidate class="space-y-6">
            @foreach($questionnaire['sections'] as $section)
            <x-self-assessment.section :title="$section['domain'] === 'DEMO' ? $section['title'] : 'Bagian ' . ($section['code'] ?? $section['domain']) . ' — ' . $section['title']" :subtitle="$section['description'] ?? null">
                @foreach($section['questions'] as $q)
                <x-self-assessment.question-card :question="$q" :number="$loop->iteration" />
                @endforeach
            </x-self-assessment.section>
            @endforeach

            <x-self-assessment.consent-section />
            
            <div x-show="submitError" x-transition class="rounded-xl border border-red-200 bg-red-50 p-4 mb-6">
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
</main>

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

        init() {
            // Initialize array for multi_choice
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

            // check consent
            if (!this.answers['consent']) {
                this.submitError = 'Anda harus menyetujui pernyataan persetujuan (consent) untuk melanjutkan.';
                return;
            }

            this.isSubmitting = true;
            
            // Format answers for API (remove UI-only fields like consent)
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
            window.location.href = window.location.pathname + '/hasil';
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
</x-layouts.dashboard>
