<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Player extends Model
{
    protected $fillable = [
        'user_id', 'name', 'nim_nip',
        'contingent_id', 'risk_lvl',
        'photo_path', 'employee_status', 'work_location'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contingent(): BelongsTo
    {
        return $this->belongsTo(Contingent::class);
    }

    public function selfAssessment(): HasOne
    {
        return $this->hasOne(SelfAssessment::class);
    }

    public function faceEmbeddings(): HasMany
    {
        return $this->hasMany(FaceEmbedding::class);
    }

    public function faceEmbedding(): HasOne
    {
        return $this->hasOne(FaceEmbedding::class)->latestOfMany();
    }

    public function photoFaces(): HasMany
    {
        return $this->hasMany(PhotoFace::class, 'matched_player_id');
    }
}
