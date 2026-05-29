<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'sport_id', 'sport_category_id',
        'registration_a_id', 'registration_b_id',
        'winner_registration_id',
        'next_match_id', 'next_match_slot',
        'loser_next_match_id', 'loser_next_match_slot',
        'is_third_place_match',
        'score_a', 'score_b',
        'status', 'round', 'round_name', 'match_number',
        'match_date', 'match_time', 'location',
        'referee_name', 'notes', 'stats',
    ];

    protected $casts = [
        'stats'               => 'array',
        'match_date'          => 'date',
        'is_third_place_match'=> 'boolean',
    ];

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function sportCategory(): BelongsTo
    {
        return $this->belongsTo(SportCategory::class);
    }

    public function registrationA(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_a_id');
    }

    public function registrationB(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_b_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'winner_registration_id');
    }

    public function nextMatch(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'next_match_id');
    }

    public function loserNextMatch(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'loser_next_match_id');
    }

    public function playerCheckins(): HasMany
    {
        return $this->hasMany(GamePlayerCheckin::class);
    }
}
