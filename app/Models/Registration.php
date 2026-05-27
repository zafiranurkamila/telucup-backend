<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Registration extends Model
{
    protected $fillable = [
        'contingent_id',
        'sport_id',
        'sport_category_id',
        'status',
    ];

    public function contingent(): BelongsTo
    {
        return $this->belongsTo(Contingent::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function sportCategory(): BelongsTo
    {
        return $this->belongsTo(SportCategory::class);
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'registration_player');
    }

    /**
     * Ambil batas maksimal anggota tim dari sport atau kategorinya.
     * Mengembalikan null jika tidak ada batas yang ditentukan.
     */
    public function maxMembers(): ?int
    {
        if ($this->sport_category_id && $this->sportCategory) {
            return $this->sportCategory->max_members;
        }

        return $this->sport ? $this->sport->max_members : null;
    }
}
