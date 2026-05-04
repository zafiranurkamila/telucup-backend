<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'sport_branch', 'round_name', 'team_a', 'team_b', 
        'score_a', 'score_b', 'winner', 'status', 
        'match_date', 'match_time', 'referee_name', 'stats',
        'round', 'match_number'
    ];

    protected $casts = [
        'stats' => 'array',
        'match_date' => 'date'
    ];
}
