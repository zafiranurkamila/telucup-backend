<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $fillable = [
        'user_id', 'name', 'nim_nip', 'sport_branch', 'contingent', 
        'checked_in_at', 'verification_status',
        'photo_path', 'employee_status', 'work_location'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function selfAssessment(): HasOne
    {
        return $this->hasOne(SelfAssessment::class);
    }

    public function faceEmbedding(): HasOne
    {
        return $this->hasOne(FaceEmbedding::class);
    }

    public function photoFaces(): HasMany
    {
        return $this->hasMany(PhotoFace::class, 'matched_player_id');
    }
}
