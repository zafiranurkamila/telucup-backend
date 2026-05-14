<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pgvector\Laravel\Vector;

class PhotoFace extends Model
{
    protected $fillable = [
        'event_photo_id',
        'matched_player_id',
        'validation_status', // 'pending', 'accepted', 'rejected'
        'similarity_score',
        'bounding_box',
        'face_encoding',
    ];

    protected $casts = [
        'bounding_box' => 'array', // Supaya otomatis jadi array saat dipanggil, json di DB
        'face_encoding' => Vector::class,
        'similarity_score' => 'float',
    ];

    public function eventPhoto(): BelongsTo
    {
        return $this->belongsTo(EventPhoto::class);
    }

    public function matchedPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'matched_player_id');
    }
}