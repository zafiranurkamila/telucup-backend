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
        'gallery_folder_id',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function photoFaces(): HasMany
    {
        return $this->hasMany(PhotoFace::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(GalleryFolder::class, 'gallery_folder_id');
    }
}