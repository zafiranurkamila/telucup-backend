<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model SelfAssessment.
 *
 * Merepresentasikan satu sesi pengisian self-assessment risiko cedera oleh
 * peserta Telucup. Setiap record berisi:
 *   - Snapshot kondisi peserta saat pengisian (sport, BMI, kacamata, dll)
 *   - Jawaban mentah (form_responses)
 *   - Skor per domain (cardio / msk / acute / psycho)
 *   - Daftar red flags & yellow flags yang terdeteksi
 *   - Klasifikasi akhir (low / medium / high)
 *   - Versi instrumen & algoritma yang digunakan (untuk audit)
 *   - Review medis (opsional, diisi oleh dokter/fisio panitia)
 *
 * Risk label menggunakan nilai: 'low', 'medium', 'high'.
 * (Nilai 'moderate' dari skema lama dipetakan ke 'medium' di Scoring Service.)
 */
class SelfAssessment extends Model
{
    protected $fillable = [
        // Relasi
        'player_id',

        // Snapshot kondisi peserta
        'sport_branch_snapshot',
        'age_snapshot',
        'bmi_snapshot',
        'is_kacamata_snapshot',

        // Versioning
        'questionnaire_version',
        'algorithm_version',

        // Jawaban mentah & analisis
        'injury_history',
        'injury_location',
        'current_condition',
        'pain_score',
        'form_responses',
        'score_breakdown',

        // Skor per domain
        'score_cardiovascular',
        'score_musculoskeletal',
        'score_acute_readiness',
        'score_psychosocial',
        'total_score',

        // Flag detection
        'red_flags',
        'yellow_flags',

        // Output sistem
        'risk_label',
        'recommendation',
        'panitia_summary',
        'requires_clearance',
        'confidence_score',
        'valid_until',

        // Review medis (filled oleh panitia medis)
        'medical_notes',
        'is_allowed_to_play',
        'reviewed_at',
        'pic_confirmed',
    ];

    protected $casts = [
        'form_responses'        => 'array',
        'score_breakdown'       => 'array',
        'red_flags'             => 'array',
        'yellow_flags'          => 'array',
        'is_allowed_to_play'    => 'boolean',
        'is_kacamata_snapshot'  => 'boolean',
        'requires_clearance'    => 'boolean',
        'pic_confirmed'         => 'boolean',
        'reviewed_at'           => 'datetime',
        'valid_until'           => 'datetime',
        'score_cardiovascular'  => 'float',
        'score_musculoskeletal' => 'float',
        'score_acute_readiness' => 'float',
        'score_psychosocial'    => 'float',
        'total_score'           => 'float',
        'bmi_snapshot'          => 'float',
        'confidence_score'      => 'float',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Cek apakah self-assessment masih berlaku.
     */
    public function isValid(): bool
    {
        if (!$this->valid_until) {
            return true;
        }
        return $this->valid_until->isFuture();
    }
}