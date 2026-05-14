<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventPhoto extends Model
{
    protected $fillable = [
        'cloudinary_public_id',
        'image_url',
        'uploaded_by',
    ];

    // Relasi ke panitia (User) yang mengupload foto lapangan
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Relasi ke banyak wajah yang terdeteksi di foto ini
    public function photoFaces(): HasMany
    {
        return $this->hasMany(PhotoFace::class);
    }
}