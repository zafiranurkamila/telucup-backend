<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SelfAssessment; // Tambahkan baris ini

class SelfAssessmentController extends Controller
{
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
