<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = [
        'title',
        'description_html',
        'type',
        'status',
        'capacity',
        'waitlist_enabled',
        'reg_start_at',
        'start_at',
        'end_at',
        'location',
    ];

    protected $casts = [
        'waitlist_enabled' => 'bool',
        'reg_start_at' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(ActivitySession::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
