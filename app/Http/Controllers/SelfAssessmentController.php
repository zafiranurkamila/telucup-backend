<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SelfAssessment; // Tambahkan baris ini

class SelfAssessmentController extends Controller
{
    /**
     * @OA\Post(
     *      path="/self-assessment",
     *      operationId="storeSelfAssessment",
     *      tags={"Self Assessment"},
     *      summary="Kirim form Self-Assessment",
     *      description="Menyimpan jawaban self-assessment dan menghitung tingkat risiko cedera menggunakan AI scoring.",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"player_id","pain_score","form_responses"},
     *              @OA\Property(property="player_id", type="integer", example=1),
     *              @OA\Property(property="injury_history", type="string", example="Cedera engkel 2 bulan lalu"),
     *              @OA\Property(property="injury_location", type="string", example="Pergelangan kaki kanan"),
     *              @OA\Property(property="current_condition", type="string", example="Sedikit nyeri saat lari"),
     *              @OA\Property(property="pain_score", type="integer", example=4),
     *              @OA\Property(
     *                  property="form_responses", type="array",
     *                  @OA\Items(type="string", example="Pertanyaan 1: Ya")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Self-Assessment Detail Berhasil Disimpan!"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
            'injury_history' => 'nullable|string',
            'injury_location' => 'nullable|string',
            'current_condition' => 'nullable|string',
            'pain_score' => 'required|integer|min:0|max:10',
            'form_responses' => 'required|array', // Data dari Bagian A-E
        ]);

        // LOGIKA AI SMART UPGRADE (FR-01.3)
        $risk = 'low';
        $recommendation = 'Pemain fit untuk bertanding.';
        
        // 1. Cek Skala Nyeri (Bagian C)
        if ($request->pain_score >= 7) {
            $risk = 'high';
            $recommendation = 'REKOMENDASI: Skala nyeri tinggi! Pemain dilarang bertanding sebelum verifikasi tim medis.';
        } elseif ($request->pain_score >= 4) {
            $risk = 'moderate';
            $recommendation = 'Pemain butuh pengawasan khusus saat pemanasan.';
        }

        // 2. Cek Riwayat Cedera (Kata Kunci)
        $history = strtolower($request->injury_history);
        if (str_contains($history, 'acl') || str_contains($history, 'patah')) {
            $risk = 'high';
            $recommendation = 'REKOMENDASI RED FLAG: Riwayat cedera berat terdeteksi. Hubungi tim medis segera.';
        }

        $game = SelfAssessment::create(array_merge($validated, [
            'risk_label' => $risk,
            'recommendation' => $recommendation,
            'confidence_score' => $risk === 'high' ? 87.4 : 95.0 // Simulasi skor keyakinan AI
        ]));

        return response()->json([
            'status' => 'success',
            'risk' => $risk,
            'confidence' => $game->confidence_score,
            'recommendation' => $recommendation,
            'message' => 'Self-Assessment Detail Berhasil Disimpan!'
        ]);
    }

    /**
     * @OA\Post(
     *      path="/self-assessment/review/{id}",
     *      operationId="submitReview",
     *      tags={"Self Assessment"},
     *      summary="Submit review medis (Untuk Dokter/Fisioterapis)",
     *      description="Menyimpan catatan medis dan status izin bertanding pemain.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"is_allowed_to_play"},
     *              @OA\Property(property="medical_notes", type="string", example="Pemain butuh istirahat 2 hari."),
     *              @OA\Property(property="is_allowed_to_play", type="boolean", example=false)
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Review medis berhasil disimpan!"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Assessment not found"
     *      )
     * )
     * Fitur Panel Peninjauan Medis (Untuk Dokter/Fisioterapis)
     */
    public function submitReview(Request $request, $id)
    {
        $assessment = SelfAssessment::findOrFail($id);
        
        $assessment->update([
            'medical_notes' => $request->medical_notes,
            'is_allowed_to_play' => $request->is_allowed_to_play,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Review medis berhasil disimpan!',
            'status' => $assessment->is_allowed_to_play ? 'Diizinkan Bermain' : 'Istirahat'
        ]);
    }
}
