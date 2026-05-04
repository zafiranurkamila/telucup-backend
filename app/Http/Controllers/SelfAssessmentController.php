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
            'injury_history' => 'required|string',
            'injury_location' => 'required|string',
            'current_condition' => 'required|string',
        ]);

        // Logika AI sederhana (FR-01.3)
        $risk = 'low';
        $recommendation = 'Pemain fit untuk bertanding.';
        $history = strtolower($request->injury_history);
        
        if (str_contains($history, 'acl') || str_contains($history, 'patah') || str_contains($history, 'operasi')) {
            $risk = 'high';
            $recommendation = 'REKOMENDASI RED FLAG: Pemain sangat disarankan untuk penggantian atau pengawasan medis ketat! (FR-01.5)';
        } elseif (str_contains($history, 'keseleo') || str_contains($history, 'memar')) {
            $risk = 'moderate';
            $recommendation = 'Pemain butuh pengawasan fisioterapi ringan.';
        }

        SelfAssessment::create(array_merge($validated, [
            'risk_label' => $risk,
            'recommendation' => $recommendation // Pastikan kolom ini ada di migrasi
        ]));

        return response()->json([
            'status' => 'success',
            'risk' => $risk,
            'recommendation' => $recommendation,
            'message' => 'Assessment berhasil disimpan.'
        ]);
    }
}
