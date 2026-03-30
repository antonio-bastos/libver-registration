<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Child extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'dob',
        'phone_emergency',
        'restrictions_until',
        'absence_count',
        'tags',
        'loyalty_points',
        'badges',
    ];

    protected $casts = [
        'dob' => 'date',
        'restrictions_until' => 'datetime',
        'tags' => 'array',
        'absence_count' => 'integer',
        'loyalty_points' => 'integer',
        'badges' => 'array',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'owner_id')->where('owner_type', 'child');
    }

    public function isRestricted(): bool
    {
        return $this->restrictions_until !== null && $this->restrictions_until->isFuture();
    }
}
