<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GdprSnapshot extends Model
{
    protected $fillable = [
        'entity_type',
        'entity_id',
        'payload',
        'captured_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'captured_at' => 'datetime',
    ];
}
