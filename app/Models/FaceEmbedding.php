<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pgvector\Laravel\Vector; // Import class Vector dari package yang baru diinstal

class FaceEmbedding extends Model
{
    protected $fillable = [
        'player_id',
        'embedding',
    ];

    protected $casts = [
        // Cast otomatis dari/ke array PHP menjadi format vektor PostgreSQL
        'embedding' => Vector::class, 
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}