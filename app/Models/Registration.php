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
    public const STATUS_PENDING_APPROVAL = 'pending_approval';

    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_PARTIAL = 'partial';
    public const PAYMENT_STATUS_UNPAID = 'unpaid';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'activity_id',
        'user_id',
        'child_id',
        'status',
        'position',
        'seat_number',
        'payment_status',
        'fee_amount',
        'amount_paid',
        'currency',
        'payment_metadata',
        'receipt_sent_at',
        'invoice_generated_at',
        'consent_media',
        'canceled_by',
        'canceled_at',
        'checked_in_at',
        'check_in_token',
        'attended',
    ];

    protected $casts = [
        'position' => 'integer',
        'fee_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'payment_metadata' => 'array',
        'receipt_sent_at' => 'datetime',
        'invoice_generated_at' => 'datetime',
        'canceled_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'attended' => 'boolean',
        'consent_media' => 'boolean',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }
}
#ss