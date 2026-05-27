<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sport extends Model
{
    protected $fillable = ['name', 'icon_path', 'max_members'];

    public function categories(): HasMany
    {
        return $this->hasMany(SportCategory::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }
}
