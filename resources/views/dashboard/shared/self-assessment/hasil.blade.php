@php
    $isPlayer = request()->user()->role === 'player';
    $roleLabel = $isPlayer ? 'Player' : 'PIC Kontingen';
@endphp

<x-layouts.dashboard :roleLabel="$roleLabel">
    <x-slot:title>Hasil Self Assessment</x-slot:title>

    <x-slot:sidebar>
        @if($isPlayer)
            @include('partials.sidebar-player')
        @else
            @include('partials.sidebar-pic')
        @endif
    </x-slot:sidebar>

@php
    $riskConfig = [
        'high' => [
            'label' => 'Risiko Tinggi',
            'description' => 'Pemain tidak direkomendasikan untuk mengikuti aktivitas intensitas tinggi.',
            'badge' => 'bg-[#B41F2A] text-white',
            'border' => 'border-[#B41F2A]',
            'bg' => 'bg-red-50',
            'text' => 'text-[#B41F2A]',
            'bar' => 'bg-[#B41F2A]',
            'headerBar' => 'bg-[#B41F2A]',
        ],
        'medium' => [
            'label' => 'Risiko Sedang',
            'description' => 'Pemain perlu pengawasan tambahan sebelum bertanding.',
            'badge' => 'bg-amber-500 text-white',
            'border' => 'border-amber-500',
            'bg' => 'bg-amber-50',
            'text' => 'text-amber-600',
            'bar' => 'bg-amber-500',
            'headerBar' => 'bg-amber-500',
        ],
        'low' => [
            'label' => 'Risiko Rendah',
            'description' => 'Pemain dalam kondisi baik dan siap untuk bertanding.',
            'badge' => 'bg-green-600 text-white',
            'border' => 'border-green-600',
            'bg' => 'bg-green-50',
            'text' => 'text-green-700',
            'bar' => 'bg-green-600',
            'headerBar' => 'bg-green-600',
        ],
    ];

    $risk = $riskConfig[$data['risk_label'] ?? 'low'];
    $redFlags = $data['red_flags'] ?? [];
    $yellowFlags = $data['yellow_flags'] ?? [];
@endphp

<main class="min-h-screen bg-[#f4f7f6] font-sans">
    <div class="mx-auto max-w-6xl px-4 py-8 lg:px-8 lg:py-10">
        <x-self-assessment-result.header 
            :playerName="$data['player_name']" 
            :contingent="$data['contingent']" 
            :sportBranch="$data['sport_branch']" 
            :createdAt="$data['created_at']" 
            :riskConfig="$risk" />

        <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
            <div class="space-y-6">
                <x-self-assessment-result.risk-summary 
                    :riskConfig="$risk" 
                    :totalScore="$data['total_score']" 
                    :requiresClearance="$data['requires_clearance']" />

                <x-self-assessment-result.domain-score :domainScores="$data['domain_scores']" />

                <x-self-assessment-result.flag-section :redFlags="$redFlags" :yellowFlags="$yellowFlags" />

                <x-self-assessment-result.recommendation :recommendation="$data['recommendation']" />
            </div>

            <div class="space-y-6">
                <x-self-assessment-result.player-info 
                    :playerName="$data['player_name']" 
                    :contingent="$data['contingent']" 
                    :sportBranch="$data['sport_branch']" 
                    :snapshot="$data['snapshot']" 
                    :assessmentId="$data['id']" />

                <x-self-assessment-result.assessment-status 
                    :isValid="$data['is_valid']" 
                    :validUntil="$data['valid_until']" 
                    :questionnaireVersion="$data['questionnaire_version']" 
                    :algorithmVersion="$data['algorithm_version']" />

                <x-self-assessment-result.medical-review :medicalReview="$data['medical_review']" />
            </div>
        </div>
    </div>
</main>
</x-layouts.dashboard>
