<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SportCategory extends Model
{
    protected $fillable = ['sport_id', 'name', 'max_members'];

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }
}
