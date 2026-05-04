<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $fillable = [
        'sport_branch', 'contingent', 'pic_name', 
        'pic_email', 'pic_whatsapp', 'status'
    ];

    public function players()
    {
        return $this->belongsToMany(Player::class, 'registration_player');
    }
}
