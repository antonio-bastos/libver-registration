<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivedActivity extends Model
{
    protected $fillable = [
        'original_activity_id',
        'title',
        'description_html',
        'type',
        'activity_subtype',
        'age_group',
        'status',
        'is_paid',
        'fee',
        'capacity',
        'seating_capacity',
        'numbered_seating',
        'waitlist_enabled',
        'requires_selection',
        'first_timers_only',
        'is_space_booking',
        'reg_start_at',
        'start_at',
        'end_at',
        'location',
        'online_url',
        'live_stream_url',
        'connection_details',
        'materials_list',
        'custom_message_postpone',
        'certificate_template',
        'auto_archive_days',
        'start_time_label',
        'metadata_json',
        'archived_at',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'numbered_seating' => 'boolean',
        'waitlist_enabled' => 'boolean',
        'requires_selection' => 'boolean',
        'first_timers_only' => 'boolean',
        'is_space_booking' => 'boolean',
        'fee' => 'decimal:2',
        'reg_start_at' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'metadata_json' => 'array',
        'archived_at' => 'datetime',
    ];
}

