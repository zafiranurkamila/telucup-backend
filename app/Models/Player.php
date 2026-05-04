<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Player extends Model
{
    protected $fillable = [
        'name', 'nim_nip', 'sport_branch', 'contingent', 
        'checked_in_at', 'verification_status',
        'photo_path', 'employee_status', 'work_location'
    ];

    public function selfAssessment(): HasOne
    {
        return $this->hasOne(SelfAssessment::class);
    }
}
