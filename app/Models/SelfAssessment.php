<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SelfAssessment extends Model
{
    protected $fillable = [
        'player_id', 'injury_history', 'injury_location', 
        'current_condition', 'risk_label', 'recommendation', 'pic_confirmed'
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}

