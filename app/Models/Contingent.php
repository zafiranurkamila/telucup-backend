<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contingent extends Model
{
    protected $fillable = ['external_id', 'name', 'faculty_name'];
}
