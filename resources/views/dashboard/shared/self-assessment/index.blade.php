@php
    $isPlayer = request()->user()->role === 'player';
    $roleLabel = $isPlayer ? 'Player' : 'PIC Kontingen';
    $sections = $questionnaire['sections'] ?? [];
    $totalSections = count($sections);
    $totalQuestions = collect($sections)->sum(fn ($section) => count($section['questions'] ?? []));
    $requiredQuestions = collect($sections)->sum(fn ($section) => collect($section['questions'] ?? [])->filter(fn ($q) => ($q['required'] ?? true) !== false)->count());
@endphp

<x-layouts.onboarding>
    <x-slot:title>Self Assessment</x-slot:title>

<section class="min-h-screen bg-[#f4f7f6]" x-data="selfAssessmentForm(@js($questionnaire), @js($activePosters), {{ $requiredQuestions }})" x-cloak>
    <div class="mx-auto max-w-6xl px-4 py-6 lg:px-8 lg:py-10">
        <div class="mb-5 flex items-center justify-between gap-4">
            <button type="button" @click="window.history.back()" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-bold text-gray-600 shadow-sm transition-colors hover:border-[#B41F2A]/30 hover:text-[#B41F2A]">
                <span class="text-lg leading-none">&larr;</span> Kembali
            </button>
            <div class="hidden items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-bold text-gray-500 shadow-sm sm:flex">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Form aktif
            </div>
        </div>

        <header class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-5 sm:px-7 lg:flex lg:items-center lg:justify-between lg:gap-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-black uppercase tracking-wide text-[#B41F2A]">Self Assessment Kesehatan</p>
                    <h1 class="mt-2 text-2xl font-black leading-tight text-gray-950 sm:text-3xl">Evaluasi kesiapan sebelum bertanding</h1>
                    <p class="mt-3 text-sm leading-relaxed text-gray-600">
                        Isi data sesuai kondisi terbaru agar panitia dapat membaca risiko kesehatan dan kebutuhan tindak lanjut dengan lebih akurat.
                    </p>
                </div>
                <div class="mt-5 grid grid-cols-3 gap-2 lg:mt-0 lg:min-w-[360px]">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Bagian</p>
                        <p class="mt-1 text-xl font-black text-gray-950">{{ $totalSections }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Pertanyaan</p>
                        <p class="mt-1 text-xl font-black text-gray-950">{{ $totalQuestions }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Wajib</p>
                        <p class="mt-1 text-xl font-black text-[#B41F2A]">{{ $requiredQuestions }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-4 sm:px-7">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-gray-900">Progress pengisian</p>
                        <p class="text-xs text-gray-500"><span x-text="answeredRequiredCount"></span> dari {{ $requiredQuestions }} pertanyaan wajib terisi</p>
                    </div>
                    <p class="text-sm font-black text-[#B41F2A]" x-text="completionPercent + '%'"></p>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-200">
                    <div class="h-full rounded-full bg-[#B41F2A] transition-all duration-500" :style="`width: ${completionPercent}%`"></div>
                </div>
            </div>
        </header>

        <form @submit.prevent="submitForm" novalidate class="space-y-6">
            @foreach($questionnaire['sections'] as $section)
                <x-self-assessment.section
                    :title="$section['domain'] === 'DEMO' ? $section['title'] : 'Bagian ' . ($section['code'] ?? $section['domain']) . ' - ' . $section['title']"
                    :subtitle="$section['description'] ?? null"
                    :code="$section['code'] ?? $section['domain'] ?? null"
                    :index="$loop->iteration"
                    :total="$totalSections"
                >
                    @foreach($section['questions'] as $q)
                        <x-self-assessment.question-card :question="$q" :number="$loop->iteration" />
                    @endforeach
                </x-self-assessment.section>
            @endforeach

            <x-self-assessment.consent-section />

            <div x-show="submitError" x-transition class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 text-red-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-red-800">Gagal Mengirim Assessment</h3>
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
    Alpine.data('selfAssessmentForm', (questionnaire, activePosters, requiredQuestionCount) => ({
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

        get requiredQuestions() {
            return questionnaire.sections.flatMap(section => section.questions).filter(q => q.required !== false);
        },

        get answeredRequiredCount() {
            return this.requiredQuestions.filter(q => this.isAnswered(q.code)).length;
        },

        get completionPercent() {
            if (!requiredQuestionCount) return 100;
            return Math.min(100, Math.round((this.answeredRequiredCount / requiredQuestionCount) * 100));
        },

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

        isAnswered(code) {
            const val = this.answers[code];
            return !(val === null || val === '' || val === undefined || (Array.isArray(val) && val.length === 0));
        },

        validate() {
            this.unansweredCodes = [];
            let isValid = true;

            questionnaire.sections.forEach(section => {
                section.questions.forEach(q => {
                    if (q.required !== false && !this.isAnswered(q.code)) {
                        this.unansweredCodes.push(q.code);
                        isValid = false;
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
                this.submitError = 'Anda harus menyetujui pernyataan persetujuan untuk melanjutkan.';
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
