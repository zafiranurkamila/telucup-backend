<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SelfAssessment;
use App\Models\SportsmanshipPoster;
use App\Services\SelfAssessment\QuestionBankService;
use Illuminate\Http\Request;

class SelfAssessmentController extends Controller
{
    private QuestionBankService $questionBank;

    public function __construct(QuestionBankService $questionBank)
    {
        $this->questionBank = $questionBank;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $player = $user->player;

        if ($player && SelfAssessment::where('player_id', $player->id)->exists() && !$request->has('re-assess')) {
            return redirect()->route(
                $user->role === 'player' ? 'dashboard.player.self-assessment.hasil' : 'dashboard.pic.self-assessment.hasil'
            );
        }

        $questionnaire = $this->questionBank->getQuestionnaire();

        $activePosters = SportsmanshipPoster::where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'description', 'image_url', 'sort_order']);

        return view('dashboard.shared.self-assessment.index', compact('questionnaire', 'activePosters'));
    }

    public function hasil(Request $request)
    {
        $user = $request->user();
        $player = $user->player;

        $assessmentId = $request->query('id');

        if ($assessmentId && is_numeric($assessmentId)) {
            $assessment = SelfAssessment::with(['player.user', 'player.contingent'])->find($assessmentId);

            if (!$assessment) {
                return redirect()->route(
                    $user->role === 'player' ? 'dashboard.player.self-assessment.index' : 'dashboard.pic.self-assessment.index'
                )->with('error', 'Self-assessment tidak ditemukan.');
            }

            // Jika bukan panitia, pastikan hanya bisa lihat miliknya sendiri atau milik anggotanya (jika pic)
            if ($user->role !== 'panitia') {
                if ($user->role === 'player' && $assessment->player) {
                    if ($assessment->player->user_id !== $user->id) {
                        abort(403, 'Anda tidak memiliki akses ke data ini.');
                    }
                }
                
                if ($user->role === 'pic_kontingen' && $player && $assessment->player) {
                    // Cek apakah player tersebut di bawah contingent pic
                    if ($assessment->player->contingent_id !== $player->contingent_id && $assessment->player->user_id !== $user->id) {
                        abort(403, 'Anda tidak memiliki akses ke data ini.');
                    }
                }
            }
        } else {
            $assessment = $player ? SelfAssessment::with(['player.user', 'player.contingent'])
                ->where('player_id', $player->id)
                ->latest()
                ->first() : null;
        }

        if (!$assessment) {
            return view('dashboard.shared.self-assessment.empty');
        }

        $data = $this->formatAssessmentResponse($assessment, $user->role === 'panitia');

        return view('dashboard.shared.self-assessment.hasil', compact('data'));
    }

    private function formatAssessmentResponse(SelfAssessment $a, bool $includeBreakdown = false): array
    {
        $base = [
            'id'                    => $a->id,
            'player_id'             => $a->player_id,
            'player_name'           => $a->player?->name,
            'sport_branch'          => $a->sport_branch_snapshot,
            'contingent'            => $a->player?->contingent?->name,
            'snapshot' => [
                'age'         => $a->age_snapshot,
                'bmi'         => $a->bmi_snapshot,
                'is_kacamata' => $a->is_kacamata_snapshot,
            ],
            'risk_label'            => $a->risk_label,
            'total_score'           => $a->total_score,
            'confidence_score'      => $a->confidence_score,
            'requires_clearance'    => $a->requires_clearance,
            'domain_scores' => [
                'cardiovascular'  => $a->score_cardiovascular,
                'musculoskeletal' => $a->score_musculoskeletal,
                'acute_readiness' => $a->score_acute_readiness,
                'psychosocial'    => $a->score_psychosocial,
            ],
            'red_flags'     => $a->red_flags ?? [],
            'yellow_flags'  => $a->yellow_flags ?? [],
            'injury_history'=> $a->injury_history ?: ($a->form_responses['B7_injury_history_description'] ?? 'Tidak ada riwayat'),
            'current_condition' => $a->current_condition,
            'pain_score'        => $a->pain_score,
            'recommendation'   => $a->recommendation,
            'panitia_summary'  => $a->panitia_summary,
            'questionnaire_version' => $a->questionnaire_version,
            'algorithm_version'     => $a->algorithm_version,
            'valid_until' => $a->valid_until,
            'is_valid'    => $a->isValid(),
            'medical_review' => [
                'medical_notes'      => $a->medical_notes,
                'is_allowed_to_play' => $a->is_allowed_to_play,
                'pic_confirmed'      => $a->pic_confirmed,
                'reviewed_at'        => $a->reviewed_at,
            ],
            'created_at' => $a->created_at,
            'updated_at' => $a->updated_at,
        ];

        if ($includeBreakdown) {
            $base['score_breakdown'] = $a->score_breakdown;
            $base['form_responses']  = $a->form_responses;
        }

        return $base;
    }
}
