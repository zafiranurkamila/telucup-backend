<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePlayerCheckin extends Model
{
    protected $fillable = [
        'game_id',
        'player_id',
        'registration_id',
        'checked_in',
        'checked_in_at',
    ];

    protected $casts = [
        'checked_in'     => 'boolean',
        'checked_in_at'  => 'datetime',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
