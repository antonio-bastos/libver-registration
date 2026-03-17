<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_WAITING = 'waiting';
    public const STATUS_OFFER_SENT = 'offer_sent';
    public const STATUS_CANCELED = 'canceled';

    protected $fillable = [
        'activity_id',
        'child_id',
        'status',
        'position',
        'seat_number',
        'canceled_by',
        'canceled_at',
    ];
    protected $casts = [
        'canceled_at' => 'datetime',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }
}
#ss