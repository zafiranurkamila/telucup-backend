<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'sport_branch',
        'team_a',
        'team_b',
        'score_a',
        'score_b',
        'winner',
        'round',
        'match_number',
    ];
}
