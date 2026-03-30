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
        'requires_selection',
        'first_timers_only',
        'is_space_booking',
        'materials_list',
        'custom_message_postpone',
        'certificate_template',
        'auto_archive_days',
        'start_time_label',
    ];

    protected $casts = [
        'waitlist_enabled' => 'bool',
        'is_active' => 'bool',
        'is_archived' => 'bool',
        'is_paid' => 'bool',
        'numbered_seating' => 'bool',
        'requires_selection' => 'bool',
        'first_timers_only' => 'bool',
        'is_space_booking' => 'bool',
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

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'owner_id')->where('owner_type', 'activity');
    }
}
