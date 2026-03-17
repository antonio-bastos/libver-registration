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
        'activity_subtype',
        'age_group',
        'status',
        'is_active',
        'is_archived',
        'capacity',
        'seating_capacity',
        'numbered_seating',
        'fee',
        'is_paid',
        'waitlist_enabled',
        'reg_start_at',
        'start_at',
        'end_at',
        'location',
        'online_url',
        'live_stream_url',
        'connection_details',
    ];

    protected $casts = [
        'waitlist_enabled' => 'bool',
        'is_active' => 'bool',
        'is_archived' => 'bool',
        'is_paid' => 'bool',
        'numbered_seating' => 'bool',
        'reg_start_at' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'fee' => 'decimal:2',
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
