<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    protected $fillable = ['name', 'categories', 'icon_path'];
    protected $casts = ['categories' => 'array'];
}
