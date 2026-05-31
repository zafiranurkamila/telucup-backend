<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryFolder extends Model
{
    protected $fillable = ['name', 'parent_id', 'created_by'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(GalleryFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(GalleryFolder::class, 'parent_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(EventPhoto::class, 'gallery_folder_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isAncestorOf(int $targetId): bool
    {
        $current = GalleryFolder::find($targetId);
        while ($current && $current->parent_id !== null) {
            if ($current->parent_id === $this->id) return true;
            $current = $current->parent;
        }
        return false;
    }
}
