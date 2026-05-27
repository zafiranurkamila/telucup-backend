<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contingent extends Model
{
    protected $fillable = ['name', 'pic_user_id'];

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }
}
