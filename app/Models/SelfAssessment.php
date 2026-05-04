<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SelfAssessment extends Model
{
    protected $fillable = [
        'player_id', 'injury_history', 'injury_location', 
        'current_condition', 'pain_score', 'form_responses', 
        'confidence_score', 'medical_notes', 'is_allowed_to_play', 'reviewed_at',
        'risk_label', 'recommendation', 'pic_confirmed'
    ];

    protected $casts = [
        'form_responses' => 'array',
        'is_allowed_to_play' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}

